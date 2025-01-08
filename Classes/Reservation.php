<?php

class Reservation {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function createReservation($userId, $vehicleId, $startDate, $endDate, $pickupLocation) {
        try {
            // Start transaction
            $this->db->begin_transaction();

            // First check if vehicle exists and is available
            $vehicleQuery = "SELECT disponibilite FROM vehicule WHERE id = ?";
            $stmt = $this->db->prepare($vehicleQuery);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $vehicle = $result->fetch_assoc();

            if (!$vehicle || !$vehicle['disponibilite']) {
                throw new Exception("Vehicle is not available");
            }

            // Check for overlapping reservations
            $overlapCheck = "SELECT COUNT(*) as count FROM reservation 
                           WHERE vehicule_id = ? 
                           AND statut != 'cancelled'
                           AND ((date_debut BETWEEN ? AND ?) 
                           OR (date_fin BETWEEN ? AND ?))";
            $stmt = $this->db->prepare($overlapCheck);
            $stmt->bind_param("issss", $vehicleId, $startDate, $endDate, $startDate, $endDate);
            $stmt->execute();
            $result = $stmt->get_result();
            $overlap = $result->fetch_assoc();

            if ($overlap['count'] > 0) {
                throw new Exception("Vehicle is already reserved for these dates");
            }

            // Create the reservation
            $query = "INSERT INTO reservation (utilisateur_id, vehicule_id, date_debut, date_fin, 
                     lieu_prise_en_charge, statut) VALUES (?, ?, ?, ?, ?, 'pending')";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("iisss", $userId, $vehicleId, $startDate, $endDate, $pickupLocation);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create reservation");
            }

            // Update vehicle availability
            $updateQuery = "UPDATE vehicule SET disponibilite = 0 WHERE id = ?";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("i", $vehicleId);
            $stmt->execute();

            // Commit transaction
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Reservation error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserReservations($userId) {
        try {
            $query = "SELECT r.*, v.marque, v.modele, v.prix_journalier, v.image_url,
                     DATEDIFF(r.date_fin, r.date_debut) as duration,
                     (DATEDIFF(r.date_fin, r.date_debut) * v.prix_journalier) as total_price
                     FROM reservation r 
                     JOIN vehicule v ON r.vehicule_id = v.id 
                     WHERE r.utilisateur_id = ? 
                     ORDER BY r.date_debut DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching reservations: " . $e->getMessage());
            return [];
        }
    }

    public function updateReservationStatus($reservationId, $status) {
        try {
            $query = "UPDATE reservation SET statut = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $status, $reservationId);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating reservation status: " . $e->getMessage());
            return false;
        }
    }

    public function getReservationById($reservationId) {
        try {
            $query = "SELECT r.*, v.marque, v.modele, v.prix_journalier, v.image_url,
                     DATEDIFF(r.date_fin, r.date_debut) as duration,
                     (DATEDIFF(r.date_fin, r.date_debut) * v.prix_journalier) as total_price
                     FROM reservation r 
                     JOIN vehicule v ON r.vehicule_id = v.id 
                     WHERE r.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $reservationId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching reservation: " . $e->getMessage());
            return null;
        }
    }

    public function cancelReservation($reservationId, $userId) {
        try {
            $this->db->begin_transaction();

            // Check if reservation exists and belongs to user
            $query = "SELECT vehicule_id, statut FROM reservation 
                     WHERE id = ? AND utilisateur_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $reservationId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $reservation = $result->fetch_assoc();

            if (!$reservation) {
                throw new Exception("Reservation not found or unauthorized");
            }

            if ($reservation['statut'] === 'cancelled') {
                throw new Exception("Reservation is already cancelled");
            }

            // Update reservation status
            $updateQuery = "UPDATE reservation SET statut = 'cancelled' WHERE id = ?";
            $stmt = $this->db->prepare($updateQuery);
            $stmt->bind_param("i", $reservationId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to cancel reservation");
            }

            // Update vehicle availability
            $updateVehicle = "UPDATE vehicule SET disponibilite = 1 WHERE id = ?";
            $stmt = $this->db->prepare($updateVehicle);
            $stmt->bind_param("i", $reservation['vehicule_id']);
            $stmt->execute();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Cancel reservation error: " . $e->getMessage());
            throw $e;
        }
    }
}