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

// Check if user is authenticated
checkAuth(2);

// Process reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    $vehicule_id = $_POST['vehicule_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $lieu_prise_en_charge = $_POST['lieu_prise_en_charge'];
    $utilisateur_id = $_SESSION['user_id']; // Get user ID from session

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

// Fetch vehicles from the database with all necessary details
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Reservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach($vehicles as $vehicle): ?>
                <div class="bg-white rounded-lg overflow-hidden shadow-md">
                    <img src="<?php echo htmlspecialchars($vehicle['image_url'] ?? '/placeholder-car.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($vehicle['marque'] . ' ' . $vehicle['modele']); ?>" 
                         class="w-full h-48 object-cover">
                    
                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-2">
                            <?php echo htmlspecialchars($vehicle['marque'] . ' ' . $vehicle['modele']); ?>
                        </h3>
                        
                        <p class="text-gray-600 mb-4">
                            <?php echo htmlspecialchars($vehicle['description']); ?>
                        </p>
                        
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-blue-600 font-semibold">
                                $<?php echo htmlspecialchars($vehicle['prix_journalier']); ?>/day
                            </span>
                            <span class="<?php echo $vehicle['disponibilite'] ? 'text-green-600' : 'text-red-600'; ?> font-semibold">
                                <?php echo $vehicle['disponibilite'] ? 'Available' : 'Not Available'; ?>
                            </span>
                        </div>

                        <?php if($vehicle['disponibilite']): ?>
                            <form action="" method="POST" class="space-y-4">
                                <input type="hidden" name="vehicule_id" value="<?php echo htmlspecialchars($vehicle['id']); ?>">
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input type="date" name="date_debut" required 
                                               min="<?php echo date('Y-m-d'); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input type="date" name="date_fin" required 
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Pickup Location</label>
                                    <select name="lieu_prise_en_charge" required 
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select location</option>
                                        <option value="Casablanca">Casablanca</option>
                                        <option value="Rabat">Rabat</option>
                                        <option value="Marrakech">Marrakech</option>
                                    </select>
                                </div>

                                <button type="submit" name="submit_reservation" value="1"
                                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors">
                                    Reserve Now
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="w-full bg-gray-400 text-white py-2 px-4 rounded-md cursor-not-allowed">
                                Not Available
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
    </script>
</body>
</html>