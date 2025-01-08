<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/databasecnx.php';
require_once 'auth.php';

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
    </div>

    <script>
        // Add client-side date validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const startDate = new Date(this.date_debut.value);
                const endDate = new Date(this.date_fin.value);
                
                if (endDate <= startDate) {
                    e.preventDefault();
                    alert('End date must be after start date');
                }
            });
        });

        // Navigation functionality
        document.addEventListener('DOMContentLoaded', function() {
    const showCars = document.getElementById('showCars');
    const showReservations = document.getElementById('showReservations');
    const carsPage = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-3').parentElement;
    const reservationsPage = document.getElementById('reservationsPage');

    // Add id to cars page container for easier reference
    carsPage.id = 'carsPage';
    
    function switchPage(showElement, hideElement, activeBtn, inactiveBtn) {
        hideElement.style.display = 'none';
        showElement.style.display = 'block';
        inactiveBtn.classList.remove('active');
        activeBtn.classList.add('active');
    }

    showCars.addEventListener('click', () => {
        switchPage(carsPage, reservationsPage, showCars, showReservations);
    });

    showReservations.addEventListener('click', () => {
        switchPage(reservationsPage, carsPage, showReservations, showCars);
    });
});

        // Prevent back button
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };
    </script>
</body>
</html>