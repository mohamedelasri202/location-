<?php
// Start session if not already started
session_start();
require_once 'config/databasecnx.php';




// Debug database connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Process reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    $vehicule_id = $_POST['vehicule_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $lieu_prise_en_charge = $_POST['lieu_prise_en_charge'];
    $utilisateur_id = $_SESSION['user_id'];

    // Check if the dates are valid
    if (strtotime($date_fin) <= strtotime($date_debut)) {
        $error = "End date must be after start date";
    } else {
        // Check if vehicle is available for these dates
        $checkAvailability = $db->prepare("
            SELECT COUNT(*) as count 
            FROM reservation 
            WHERE vehicule_id = ? 
            AND ((date_debut BETWEEN ? AND ?) 
            OR (date_fin BETWEEN ? AND ?))
            AND statut != 'cancelled'
        ");
        
        $checkAvailability->bind_param("issss", 
            $vehicule_id, 
            $date_debut, 
            $date_fin,
            $date_debut,
            $date_fin
        );
        
        $checkAvailability->execute();
        $result = $checkAvailability->get_result();
        $count = $result->fetch_assoc()['count'];

        if ($count > 0) {
            $error = "Vehicle is not available for selected dates";
        } else {
            // Insert the reservation
            $insertReservation = $db->prepare("
                INSERT INTO reservation 
                (utilisateur_id, vehicule_id, date_debut, date_fin, lieu_prise_en_charge, statut) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $insertReservation->bind_param("iisss", 
                $utilisateur_id,
                $vehicule_id,
                $date_debut,
                $date_fin,
                $lieu_prise_en_charge
            );

            if ($insertReservation->execute()) {
                $success = "Reservation submitted successfully!";
            } else {
                $error = "Error creating reservation: " . $db->error;
            }
        }
    }
}

// Fetch vehicles from the database
$vehiclesQuery = "
    SELECT id, marque, modele, description, prix_journalier, image_url,
    NOT EXISTS (
        SELECT 1 FROM reservation 
        WHERE vehicule.id = reservation.vehicule_id 
        AND statut != 'cancelled'
        AND CURDATE() BETWEEN date_debut AND date_fin
    ) as disponibilite
    FROM vehicule
";

$result = $db->query($vehiclesQuery);
$vehicles = $result->fetch_all(MYSQLI_ASSOC);

// Debug vehicles
if (!$vehicles) {
    error_log("Vehicles query error: " . $db->error);
    $vehicles = [];
}

// Fetch reservations
$reservationsQuery = "
    SELECT 
        r.id,
        r.date_debut,
        r.date_fin,
        r.statut,
        r.lieu_prise_en_charge,
        v.marque,
        v.modele,
        v.prix_journalier,
        v.image_url,
        DATEDIFF(r.date_fin, r.date_debut) as duration
    FROM reservation r
    JOIN vehicule v ON r.vehicule_id = v.id
    WHERE r.utilisateur_id = ?
    ORDER BY r.date_debut DESC
";

$stmt = $db->prepare($reservationsQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$userReservations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug reservations
if (!$userReservations) {
    error_log("Reservations query error: " . $db->error);
    $userReservations = [];
}







// Include your database connection

// Process article submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_article'])) {
    try {
        // Start transaction
        $db->begin_transaction();
        
        // Get form data
        $title = $_POST['title'];
        $theme_id = $_POST['theme_id'];
        $content = $_POST['content'];
        $user_id = $_SESSION['user_id'];
        $tags = !empty($_POST['tags']) ? explode(',', $_POST['tags']) : [];
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['article_image']) && $_FILES['article_image']['error'] == 0) {
            $upload_dir = 'uploads/articles/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['article_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('article_') . '.' . $file_extension;
            $image_path = $upload_dir . $file_name;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['article_image']['tmp_name'], $image_path)) {
                throw new Exception("Failed to upload image");
            }
        }
        
        // Insert article
        $article_query = $db->prepare("
            INSERT INTO Article (titre, contenu, dateCreation, statut, utilisateur_id, theme_id, image_url) 
            VALUES (?, ?, NOW(), 'published', ?, ?, ?)
        ");
        
        $article_query->bind_param("ssiis", $title, $content, $user_id, $theme_id, $image_path);
        
        if (!$article_query->execute()) {
            throw new Exception("Failed to insert article: " . $db->error);
        }
        
        $article_id = $db->insert_id;
        
        // Handle tags
        if (!empty($tags)) {
            foreach ($tags as $tag_name) {
                // Clean tag name
                $tag_name = trim($tag_name);
                
                // First, try to find if tag exists
                $find_tag = $db->prepare("SELECT id FROM Tag WHERE nom = ?");
                $find_tag->bind_param("s", $tag_name);
                $find_tag->execute();
                $result = $find_tag->get_result();
                
                if ($result->num_rows > 0) {
                    // Tag exists, get its ID
                    $tag_id = $result->fetch_assoc()['id'];
                } else {
                    // Tag doesn't exist, create it
                    $create_tag = $db->prepare("INSERT INTO Tag (nom) VALUES (?)");
                    $create_tag->bind_param("s", $tag_name);
                    
                    if (!$create_tag->execute()) {
                        throw new Exception("Failed to create tag: " . $db->error);
                    }
                    
                    $tag_id = $db->insert_id;
                }
                
                // Link article and tag in Article_Tag table
                $link_tag = $db->prepare("INSERT INTO Article_Tag (article_id, tag_id) VALUES (?, ?)");
                $link_tag->bind_param("ii", $article_id, $tag_id);
                
                if (!$link_tag->execute()) {
                    throw new Exception("Failed to link article and tag: " . $db->error);
                }
            }
        }
        
        // Commit transaction
        $db->commit();
        
        $_SESSION['success_message'] = "Article published successfully!";
        
        // Redirect to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollback();
        
        // Delete uploaded image if it exists
        if (!empty($image_path) && file_exists($image_path)) {
            unlink($image_path);
        }
        
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Display success/error messages
if (isset($_SESSION['success_message'])) {
    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">' . 
         htmlspecialchars($_SESSION['success_message']) . 
         '</div>';
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">' . 
         htmlspecialchars($_SESSION['error_message']) . 
         '</div>';
    unset($_SESSION['error_message']);
}

// First, fetch articles with user information
$articlesQuery = "
    SELECT 
        a.id,
        a.titre,
        a.image_url,
        a.contenu,
        a.dateCreation,
        t.theme_name,
        u.nom as username,  -- Changed from u.username to u.nom
        GROUP_CONCAT(tag.nom) as tags
    FROM Article a
    JOIN Theme t ON a.theme_id = t.id
    JOIN utilisateur u ON a.utilisateur_id = u.id  -- Changed Utilisateur to utilisateur (case sensitive)
    LEFT JOIN Article_Tag at ON a.id = at.article_id
    LEFT JOIN Tag tag ON at.tag_id = tag.id
    GROUP BY a.id, a.titre, a.image_url, a.contenu, a.dateCreation, t.theme_name, u.nom
    ORDER BY a.dateCreation DESC
";

// Debug the query
try {
    $result = $db->query($articlesQuery);
    if (!$result) {
        throw new Exception($db->error);
    }
    $articles = $result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    $articles = [];
}












// 


function getThemes($db) {
    $themes = array();
    $query = "SELECT id, theme_name FROM Theme ORDER BY theme_name ASC";
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $themes[] = $row;
        }
    }
    return $themes;
}

// Get database connection using your existing class
$db = (new ConnectData())->getConnection();

// Fetch themes
$themes = getThemes($db);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Car Rental Service</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
        }
        
        .nav-link {
            position: relative;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #1d4ed8;
            transition: width 0.3s ease;
        }
        
        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }
        
        .card {
            transition: all 0.3s ease;
            background: linear-gradient(145deg, #ffffff, #f3f4f6);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1);
        }

        .logo-text {
            background: linear-gradient(135deg, #000000 0%, #1e3a8a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .reserved-badge {
            animation: pulse 2s infinite;
            box-shadow: 0 0 15px rgba(251, 146, 60, 0.5);
        }

        .page {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        .page.active {
            display: block;
        }

        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        #carsPage {
            display: block; /* Make cars page visible by default */
        }
        
    </style>
</head>
<body class="bg-gray-100" style="scroll-behavior: smooth;">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed z-50 w-full">
        <div class="max-w-7xl mx-auto px-8 py-6">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-car-side text-4xl bg-gradient-to-r from-black to-blue-900 text-transparent bg-clip-text"></i>
                    <div>
                        <span class="text-3xl font-bold logo-text tracking-wider">Loca</span>
                        <span class="text-3xl font-black logo-text">Auto</span>
                    </div>
                </div>
                
                <!-- Navigation -->
                <div class="flex items-center space-x-12">
                    <div class="flex space-x-8">
                        <button id="showCars" class="nav-link active text-lg font-semibold hover:text-blue-800 transition-colors flex items-center">
                            <i class="fa-solid fa-car-rear mr-2"></i>
                            Available Cars
                        </button>
                        <button id="showReservations" class="nav-link text-lg font-semibold hover:text-blue-800 transition-colors flex items-center">
                            <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                            My Reservations
                        </button>
                        <!-- In the navigation div where "Available Cars" and "My Reservations" buttons are -->
<button id="showBlog" class="nav-link text-lg font-semibold hover:text-blue-800 transition-colors flex items-center">
    <i class="fa-solid fa-blog mr-2"></i>
    Blog
</button>
                    </div>

                    
                    <div class="flex items-center space-x-6">
                        <a href="controllers/logout.php">
                            <button class="bg-black hover:bg-gray-800 text-white px-6 py-2.5 rounded-full transition-colors duration-300 flex items-center space-x-2">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Logout</span>
                            </button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- ********************************** -->
     <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="margin-top: 100px;">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Cars Page -->
        <div id="carsPage" class="page active">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach($vehicles as $vehicle): ?>
                    <div class="card rounded-2xl shadow-lg overflow-hidden">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($vehicle['image_url'] ?? '/placeholder-car.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($vehicle['marque'] . ' ' . $vehicle['modele']); ?>" 
                                 class="w-full h-56 object-cover">
                            
                            <span class="absolute top-4 right-4 <?php echo $vehicle['disponibilite'] ? 'bg-gradient-to-r from-green-400 to-green-600' : 'bg-gradient-to-r from-orange-400 to-orange-600 reserved-badge'; ?> text-white px-4 py-1.5 rounded-full font-semibold shadow-lg">
                                <?php echo $vehicle['disponibilite'] ? 'Available' : 'Reserved'; ?>
                            </span>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <h3 class="font-bold text-2xl text-gray-800">
                                <?php echo htmlspecialchars($vehicle['marque'] . ' ' . $vehicle['modele']); ?>
                            </h3>
                            
                            <p class="text-gray-600">
                                <?php echo htmlspecialchars($vehicle['description']); ?>
                            </p>
                            
                            <div class="text-blue-600 font-semibold">
                                $<?php echo htmlspecialchars($vehicle['prix_journalier']); ?>/day
                            </div>

                            <?php if($vehicle['disponibilite']): ?>
                                <form action="" method="POST" class="space-y-4">
                                    <input type="hidden" name="vehicule_id" value="<?php echo htmlspecialchars($vehicle['id']); ?>">
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="relative">
                                            <i class="fa-regular fa-calendar absolute left-3 top-3 text-blue-600"></i>
                                            <input type="date" name="date_debut" required 
                                                   min="<?php echo date('Y-m-d'); ?>"
                                                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>
                                        <div class="relative">
                                            <i class="fa-regular fa-calendar-check absolute left-3 top-3 text-blue-600"></i>
                                            <input type="date" name="date_fin" required 
                                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <i class="fa-solid fa-location-dot absolute left-3 top-3 text-blue-600"></i>
                                        <select name="lieu_prise_en_charge" required 
                                                class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                                            <option value="">Select location</option>
                                            <option value="Casablanca">Casablanca</option>
                                            <option value="Rabat">Rabat</option>
                                            <option value="Marrakech">Marrakech</option>
                                        </select>
                                    </div>

                                    <button type="submit" name="submit_reservation" value="1"
                                            class="w-full bg-gradient-to-r from-blue-600 to-blue-800 text-white py-3 rounded-lg hover:from-blue-700 hover:to-blue-900 transition-all duration-300 font-semibold shadow-md">
                                        Reserve Now
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="text-orange-500 text-center font-medium bg-orange-50 p-3 rounded-lg">
                                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                                    Currently Reserved
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- ******************************************** -->

  <!-- Reservations Page -->
  <div id="reservationsPage" class="page">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h2 class="text-3xl font-bold text-gray-800 mb-8">My Reservations</h2>
                
                <?php if (empty($userReservations)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-calendar-times text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-600 text-xl">You don't have any reservations yet.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach($userReservations as $reservation): ?>
                            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                <div class="flex flex-col md:flex-row">
                                    <!-- Car Image -->
                                    <div class="md:w-1/3">
                                        <img src="<?php echo htmlspecialchars($reservation['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>"
                                             class="w-full h-64 md:h-full object-cover">
                                    </div>
                                    
                                    <!-- Reservation Details -->
                                    <div class="md:w-2/3 p-6 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start mb-4">
                                                <h3 class="text-2xl font-bold text-gray-800">
                                                    <?php echo htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']); ?>
                                                </h3>
                                                <span class="<?php
                                                    echo match($reservation['statut']) {
                                                        'confirmed' => 'bg-green-100 text-green-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                        default => 'bg-yellow-100 text-yellow-800'
                                                    };
                                                ?> px-4 py-1 rounded-full text-sm font-semibold">
                                                    <?php echo ucfirst(htmlspecialchars($reservation['statut'])); ?>
                                                </span>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                                <div>
                                                    <p class="text-gray-600">
                                                        <i class="fas fa-map-marker-alt text-blue-600 mr-2"></i>
                                                        Location: <?php echo htmlspecialchars($reservation['lieu_prise_en_charge']); ?>
                                                    </p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-600">
                                                        <i class="fas fa-tag text-blue-600 mr-2"></i>
                                                        Price: $<?php echo htmlspecialchars($reservation['prix_journalier']); ?>/day
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="space-y-2">
                                                    <div class="flex items-center">
                                                        <i class="far fa-calendar text-blue-600 mr-2"></i>
                                                        <span class="text-gray-600">Start Date:</span>
                                                        <span class="ml-2 font-semibold">
                                                            <?php echo date('M d, Y', strtotime($reservation['date_debut'])); ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="far fa-calendar-check text-blue-600 mr-2"></i>
                                                        <span class="text-gray-600">End Date:</span>
                                                        <span class="ml-2 font-semibold">
                                                            <?php echo date('M d, Y', strtotime($reservation['date_fin'])); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="space-y-2">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-clock text-blue-600 mr-2"></i>
                                                        <span class="text-gray-600">Duration:</span>
                                                        <span class="ml-2 font-semibold">
                                                            <?php echo $reservation['duration']; ?> days
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-money-bill-wave text-blue-600 mr-2"></i>
                                                        <span class="text-gray-600">Total Price:</span>
                                                        <span class="ml-2 font-semibold">
                                                            $<?php echo $reservation['duration'] * $reservation['prix_journalier']; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
       <!-- Blog Page -->
<div id="blogPage" class="page">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Blog Articles</h2>
            <button id="showAddArticle" 
                    class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-2.5 rounded-full transition-colors duration-300 flex items-center space-x-2">
                <i class="fa-solid fa-plus"></i>
                <span>Add Article</span>
            </button>
        </div>

        <!-- Add Article Form -->
        <div id="addArticleForm" class="hidden bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-2xl font-bold text-gray-800 mb-6">New Article</h3>
            <form class="space-y-6" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-gray-700 font-medium">Title</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none"
                               placeholder="Enter article title">
                    </div>
                    <div class="space-y-2">
                        <label class="text-gray-700 font-medium">Theme</label>
                        <select name="theme_id" required 
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none">
                            <option value="">Select a theme</option>
                            <?php foreach ($themes as $theme): ?>
                                <option value="<?php echo htmlspecialchars($theme['id']); ?>">
                                    <?php echo htmlspecialchars($theme['theme_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-gray-700 font-medium">Article Image</label>
                    <div class="flex flex-col space-y-2">
                        <div class="relative border-2 border-dashed border-gray-300 rounded-lg p-6">
                            <input type="file" name="article_image" required accept="image/*"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   onchange="previewImage(this)">
                            <div class="text-center" id="upload-text">
                                <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-400">PNG, JPG, GIF up to 5MB</p>
                            </div>
                            <img id="image-preview" class="hidden max-h-48 mx-auto">
                        </div>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-gray-700 font-medium">Content</label>
                    <textarea name="content" required
                              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none h-32"
                              placeholder="Write your article content here"></textarea>
                </div>

                <div class="space-y-2">
                    <label class="text-gray-700 font-medium">Tags</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="tagInput" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-300 focus:outline-none"
                               placeholder="Add tags (press Enter to add)">
                        <button type="button" id="addTagBtn"
                                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Add Tag
                        </button>
                    </div>
                    <!-- Tags display container -->
                    <div id="tagsContainer" class="flex flex-wrap gap-2 mt-2">
                        <!-- Tags will be displayed here -->
                    </div>
                    <!-- Hidden input to store tags for form submission -->
                    <input type="hidden" name="tags" id="tagsInput">
                </div>

                <div class="flex justify-end space-x-4">
                    <button type="button" id="cancelArticle"
                            class="px-6 py-2.5 border border-gray-300 rounded-full hover:bg-gray-50 transition-colors duration-300">
                        Cancel
                    </button>
                    <button type="submit" name="submit_article"
                            class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-2.5 rounded-full hover:from-blue-700 hover:to-blue-900 transition-all duration-300">
                        Publish Article
                    </button>
                </div>
            </form>
        </div>

        <!-- Articles Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach($articles as $article): 
        $tags = $article['tags'] ? explode(',', $article['tags']) : [];
    ?>
        <div class="card rounded-2xl shadow-lg overflow-hidden">
            <!-- Author info at the top -->
            <div class="p-4 bg-gray-50 flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                    <?php echo strtoupper(substr($article['username'], 0, 1)); ?>
                </div>
                <span class="font-medium text-gray-700"><?php echo htmlspecialchars($article['username']); ?></span>
            </div>
            
            <!-- Article title -->
            <h3 class="font-bold text-2xl text-gray-800 px-6 pt-4">
                <?php echo htmlspecialchars($article['titre']); ?>
            </h3>
            
            <!-- Tags -->
            <div class="px-6 pt-2 flex flex-wrap gap-2">
                <?php foreach($tags as $tag): ?>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm">
                        <?php echo htmlspecialchars(trim($tag)); ?>
                    </span>
                <?php endforeach; ?>
            </div>
            
            <!-- Image -->
            <div class="relative mt-4">
                <img src="<?php echo htmlspecialchars($article['image_url'] ?? '/api/placeholder/400/300'); ?>" 
                     alt="<?php echo htmlspecialchars($article['titre']); ?>" 
                     class="w-full h-48 object-cover">
                <span class="absolute top-4 right-4 bg-blue-500 text-white px-4 py-1.5 rounded-full font-semibold">
                    <?php echo htmlspecialchars($article['theme_name']); ?>
                </span>
            </div>
            
            <!-- Content preview and read more -->
            <div class="p-6 space-y-4">
                <p class="text-gray-600">
                    <?php echo substr(htmlspecialchars($article['contenu']), 0, 100) . '...'; ?>
                </p>
                <button class="text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                    Read More →
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div>
</div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
    // Page Elements
    const elements = {
        buttons: {
            showCars: document.getElementById('showCars'),
            showReservations: document.getElementById('showReservations'),
            showBlog: document.getElementById('showBlog'),
            showAddArticle: document.getElementById('showAddArticle'),
            cancelArticle: document.getElementById('cancelArticle'),
            addTagBtn: document.getElementById('addTagBtn')
        },
        pages: {
            cars: document.getElementById('carsPage'),
            reservations: document.getElementById('reservationsPage'),
            blog: document.getElementById('blogPage')
        },
        forms: {
            addArticle: document.getElementById('addArticleForm')
        },
        inputs: {
            tagInput: document.getElementById('tagInput'),
            tagsContainer: document.getElementById('tagsContainer'),
            hiddenTagsInput: document.getElementById('tagsInput'),
            imagePreview: document.getElementById('image-preview'),
            uploadText: document.getElementById('upload-text')
        }
    };

    // State Management
    let tags = [];

    // Navigation Handler
    function switchPage(activeButton) {
        // Get all navigation buttons and pages
        const allButtons = [
            elements.buttons.showCars, 
            elements.buttons.showReservations, 
            elements.buttons.showBlog
        ];
        const allPages = [
            elements.pages.cars, 
            elements.pages.reservations, 
            elements.pages.blog
        ];

        // Reset all states
        allButtons.forEach(btn => btn.classList.remove('active'));
        allPages.forEach(page => page.style.display = 'none');

        // Set active states
        activeButton.classList.add('active');

        // Show corresponding page
        switch(activeButton) {
            case elements.buttons.showCars:
                elements.pages.cars.style.display = 'block';
                break;
            case elements.buttons.showReservations:
                elements.pages.reservations.style.display = 'block';
                break;
            case elements.buttons.showBlog:
                elements.pages.blog.style.display = 'block';
                break;
        }
    }

    // Tag Management
    function addTag(tagText) {
        tagText = tagText.trim();
        if (tagText && !tags.includes(tagText)) {
            tags.push(tagText);
            const tagElement = document.createElement('div');
            tagElement.className = 'bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm flex items-center gap-2';
            tagElement.innerHTML = `
                ${tagText}
                <button type="button" class="hover:text-blue-900 font-bold" onclick="removeTag(this, '${tagText}')">&times;</button>
            `;
            elements.inputs.tagsContainer.appendChild(tagElement);
            updateHiddenInput();
        }
    }

    function removeTag(button, tagText) {
        button.parentElement.remove();
        tags = tags.filter(tag => tag !== tagText);
        updateHiddenInput();
    }

    function updateHiddenInput() {
        elements.inputs.hiddenTagsInput.value = tags.join(',');
    }

    // Image Preview Handler
    function handleImagePreview(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                elements.inputs.imagePreview.src = e.target.result;
                elements.inputs.imagePreview.classList.remove('hidden');
                elements.inputs.uploadText.classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Form Validation
    function validateForm(form) {
        const startDate = new Date(form.date_debut?.value);
        const endDate = new Date(form.date_fin?.value);
        
        if (endDate <= startDate) {
            alert('End date must be after start date');
            return false;
        }
        
        if (form.hasAttribute('data-requires-tags') && tags.length === 0) {
            alert('Please add at least one tag');
            return false;
        }
        
        return true;
    }

    // Event Listeners
    function initializeEventListeners() {
        // Navigation
        elements.buttons.showCars?.addEventListener('click', () => switchPage(elements.buttons.showCars));
        elements.buttons.showReservations?.addEventListener('click', () => switchPage(elements.buttons.showReservations));
        elements.buttons.showBlog?.addEventListener('click', () => switchPage(elements.buttons.showBlog));

        // Article Form
        elements.buttons.showAddArticle?.addEventListener('click', () => {
            elements.forms.addArticle.classList.remove('hidden');
            elements.forms.addArticle.classList.add('animate-fadeIn');
        });

        elements.buttons.cancelArticle?.addEventListener('click', () => {
            elements.forms.addArticle.classList.add('hidden');
            elements.forms.addArticle.classList.remove('animate-fadeIn');
        });

        // Tag Management
        elements.buttons.addTagBtn?.addEventListener('click', () => {
            addTag(elements.inputs.tagInput.value);
            elements.inputs.tagInput.value = '';
        });

        elements.inputs.tagInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addTag(this.value);
                this.value = '';
            }
        });

        // Form Validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                }
            });
        });

        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function() {
            history.pushState(null, null, location.href);
        };
    }

    // Make removeTag available globally
    window.removeTag = removeTag;
    // Make handleImagePreview available globally
    window.previewImage = handleImagePreview;

    // Initialize all event listeners
    initializeEventListeners();
});
    </script>
</body>
</html>