<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load required files
require_once __DIR__ . '/../config/databasecnx.php'; // Adjust path as needed
require_once __DIR__ . '/../auth.php'; // Adjust path as needed

// Ensure user is logged in
checkAuth(2);

// Only process reservation submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    try {
        // Get database connection
        $db = (new ConnectData())->getConnection();
        if (!$db) {
            throw new Exception("Database connection failed");
        }

        // Validate and sanitize inputs
        $vehicule_id = filter_input(INPUT_POST, 'vehicule_id', FILTER_SANITIZE_NUMBER_INT);
        $utilisateur_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
        $date_debut = filter_input(INPUT_POST, 'date_debut', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $date_fin = filter_input(INPUT_POST, 'date_fin', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $lieu_prise_en_charge = filter_input(INPUT_POST, 'lieu_prise_en_charge', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        // Validate required fields
        if (!$vehicule_id || !$utilisateur_id || !$date_debut || !$date_fin || !$lieu_prise_en_charge) {
            throw new Exception("All fields are required");
        }

        // Start transaction
        $db->begin_transaction();

        // Insert reservation (without checking availability)
        $insert_query = "INSERT INTO reservation (utilisateur_id, vehicule_id, date_debut, date_fin, lieu_prise_en_charge, statut) 
                        VALUES (?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $db->prepare($insert_query);
        if (!$stmt) {
            throw new Exception("Failed to prepare insert query: " . $db->error);
        }

        $stmt->bind_param("iisss", $utilisateur_id, $vehicule_id, $date_debut, $date_fin, $lieu_prise_en_charge);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create reservation: " . $stmt->error);
        }

        // Commit transaction
        $db->commit();

        // Set success message
        $_SESSION['success'] = "Reservation successful! We'll contact you shortly to confirm.";

    } catch (Exception $e) {
        // Rollback the transaction if something goes wrong
        if (isset($db)) {
            $db->rollback();
        }

        // Log the error
        error_log("Error: " . $e->getMessage());

        // Set the error message in session
        $_SESSION['error'] = "Error: " . $e->getMessage();
        
        // Redirect to index page after error handling
        header("Location: ../index.php");
        exit();
    }
    
    // Redirect on successful reservation
    header("Location: ../index.php");
    exit();
} else {
    // Log if the form is not submitted correctly
    error_log("Invalid request: Method=" . $_SERVER['REQUEST_METHOD'] . ", submit_reservation=" . (isset($_POST['submit_reservation']) ? 'set' : 'not set'));
    
    // Set invalid request error message
    $_SESSION['error'] = "Invalid request";
    
    // Redirect to index page
    header("Location: ../index.php");
    exit();
}
