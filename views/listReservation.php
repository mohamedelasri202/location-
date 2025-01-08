<?php
require_once '../config/databasecnx.php';
require_once '../auth.php';

// Check if user is logged in and is an admin
$db = (new ConnectData())->getConnection();

// Update reservation status if requested
if (isset($_POST['action']) && isset($_POST['reservation_id'])) {
    $valid_statuses = ['pending', 'confirmed', 'cancelled'];
    $new_status = $_POST['action'];
    $reservation_id = $_POST['reservation_id'];
    
    if (in_array($new_status, $valid_statuses)) {
        $stmt = $db->prepare("UPDATE reservation SET statut = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $reservation_id);
        if ($stmt->execute()) {
            // Optional: Add success message
            $success_message = "Reservation status updated successfully!";
        } else {
            // Optional: Add error message
            $error_message = "Failed to update reservation status.";
        }
        // Redirect to prevent form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
// Fetch all reservations with vehicle and user details
$query = "
    SELECT 
        r.id,
        r.date_debut,
        r.date_fin,
        r.statut,
        r.lieu_prise_en_charge,
        v.marque,
        v.modele,
        v.image_url,
        u.nom as user_name
    FROM reservation r
    JOIN vehicule v ON r.vehicule_id = v.id
    JOIN utilisateur u ON r.utilisateur_id = u.id
    ORDER BY r.date_debut DESC
";

$result = $db->query($query);
$reservations = $result->fetch_all(MYSQLI_ASSOC);

// Count reservations by status
$status_counts = [
    'total' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'cancelled' => 0
];

foreach ($reservations as $reservation) {
    $status_counts['total']++;
    $status_counts[$reservation['statut']]++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href=".././assets/style.css">
    <style>
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s ease;
        }
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        .status-confirmed {
            background-color: #D4EDDA;
            color: #155724;
        }
        .status-cancelled {
            background-color: #F8D7DA;
            color: #721C24;
        }
        .status-controls i {
            margin: 0 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .status-controls i:hover {
            transform: scale(1.2);
        }
    </style>
</head>

<body>
    <!-- Side Bar -->
    <div class="fixed top-0 left-0 w-[230px] h-[100%] z-50 overflow-hidden sidebar">
        <a href="" class="logo text-xl font-bold h-[56px] flex items-center text-[#1976D2] z-30 pb-[20px] box-content">
            <i class="mt-4 text-xxl max-w-[60px] flex justify-center"><i class="fa-solid fa-car-side"></i></i>
            <div class="logoname ml-2"><span>Loca</span>Auto</div>
        </a>
        <ul class="side-menu w-full mt-12">
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1"><a href="listClients.php" class="menu-item"><i class="fa-solid fa-user-group"></i>Clients</a></li>
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1"><a href="listCars.php" class="menu-item"><i class="fa-solid fa-car"></i>Cars</a></li>
            <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1"><a href="categories.php" class="menu-item"><i class="fa-solid fa-file-contract"></i>Categories</a></li>
            <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1"><a href="listReservation.php" class="menu-item"><i class="fa-solid fa-calendar-check"></i>Reservations</a></li>
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href="listThemes.php" class="menu-item"><i class="fa-solid fa-layer-group"></i>Themes</a>
        </li>
        <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href="listArticle.php" class="menu-item"><i class="fa-solid fa-newspaper"></i>Articles</a>
        </li>
        <li class="h-12 bg-transparent ml-1.5 rounded-l-full p-1">
            <a href="listTags.php" class="menu-item"><i class="fa-solid fa-tags"></i>Tags</a>
        </li>
        <li class="active h-12 bg-transparent ml-1.5 rounded-l-full p-1">
            <a href="listCommentaires.php" class="menu-item"><i class="fa-solid fa-comments"></i>Comments</a>
        </li>
             <ul class="side-menu w-full mt-12">
            <li class="h-12 bg-transparent ml-2.5 rounded-l-full p-1">
            <a href=".././controllers/logout.php" class="logout">
                    <i class='bx bx-log-out-circle'></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Content -->
    <div class="content">
        <!-- Navbar -->
        <nav class="flex items-center gap-6 h-14 bg-[#f6f6f9] sticky top-0 left-0 z-50 px-6">
            <i class='bx bx-menu'></i>
            <form action="#" class="max-w-[400px] w-full mr-auto">
                <div class="form-input flex items-center h-[36px]">
                    <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search reservations...">
                    <button class="w-[80px] h-full flex justify-center items-center bg-[#1976D2] text-[#f6f6f9] text-[18px] border-0 outline-none rounded-r-[36px] cursor-pointer" type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
        </nav>

        <!-- Main Content -->
        <main class="mainn w-full p-[36px_24px] max-h-[calc(100vh_-_56px)]">
            <!-- Insights -->
            <ul class="insights grid grid-cols-[repeat(auto-fit,_minmax(240px,_1fr))] gap-[24px] mt-[36px]">
                <li>
                    <i class="fa-solid fa-calendar-check"></i>
                    <span class="info">
                        <h3><?= $status_counts['total'] ?></h3>
                        <p>Total Reservations</p>
                    </span>
                </li>
                <li>
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span class="info">
                        <h3><?= $status_counts['pending'] ?></h3>
                        <p>Pending</p>
                    </span>
                </li>
                <li>
                    <i class="fa-solid fa-circle-check"></i>
                    <span class="info">
                        <h3><?= $status_counts['confirmed'] ?></h3>
                        <p>Confirmed</p>
                    </span>
                </li>
                <li>
                    <i class="fa-solid fa-ban"></i>
                    <span class="info">
                        <h3><?= $status_counts['cancelled'] ?></h3>
                        <p>Cancelled</p>
                    </span>
                </li>
            </ul>

            <!-- Reservations Table -->
            <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Reservations Table -->
    <div class="bottom-data flex flex-wrap gap-[24px] mt-[24px] w-full">
        <div class="orders flex-grow flex-[1_0_500px]">
            <div class="header flex items-center gap-[16px] mb-[24px]">
                <i class='bx bx-list-check'></i>
                <h3 class="mr-auto text-[24px] font-semibold">Reservations List</h3>
                <i class='bx bx-filter'></i>
                <i class='bx bx-search'></i>
            </div>

            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Car</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Client</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Location</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Start Date</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">End Date</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Status</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td class="py-4 px-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-16 h-16 overflow-hidden rounded">
                                        <img src="../<?= htmlspecialchars($reservation['image_url']) ?>" 
                                             alt="Car" 
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-semibold"><?= htmlspecialchars($reservation['marque']) ?></div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($reservation['modele']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-3"><?= htmlspecialchars($reservation['user_name']) ?></td>
                            <td class="py-4 px-3"><?= htmlspecialchars($reservation['lieu_prise_en_charge']) ?></td>
                            <td class="py-4 px-3"><?= date('M d, Y', strtotime($reservation['date_debut'])) ?></td>
                            <td class="py-4 px-3"><?= date('M d, Y', strtotime($reservation['date_fin'])) ?></td>
                            <td class="py-4 px-3">
                                <span class="status-badge status-<?= $reservation['statut'] ?>">
                                    <?php if ($reservation['statut'] === 'pending'): ?>
                                        <i class="fas fa-clock"></i>
                                    <?php elseif ($reservation['statut'] === 'confirmed'): ?>
                                        <i class="fas fa-check-circle"></i>
                                    <?php else: ?>
                                        <i class="fas fa-ban"></i>
                                    <?php endif; ?>
                                    <?= ucfirst($reservation['statut']) ?>
                                </span>
                            </td>
                            <td class="py-4 px-3">
                                <form method="POST" class="inline-flex space-x-2" id="form_<?= $reservation['id'] ?>">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" 
                                            name="action" 
                                            value="confirmed" 
                                            class="text-green-600 hover:text-green-800 transition-colors duration-200" 
                                            title="Confirm"
                                            <?= $reservation['statut'] === 'confirmed' ? 'disabled' : '' ?>>
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button type="submit" 
                                            name="action" 
                                            value="cancelled" 
                                            class="text-red-600 hover:text-red-800 transition-colors duration-200" 
                                            title="Cancel"
                                            <?= $reservation['statut'] === 'cancelled' ? 'disabled' : '' ?>>
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                    <a href="#" 
                                       class="text-blue-600 hover:text-blue-800 transition-colors duration-200" 
                                       title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Add any JavaScript functionality you need here
        document.querySelector('.bx-menu').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>