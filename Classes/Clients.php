<?php
require_once '../config/databasecnx.php';


// Add this new class to handle admin user operations
class AdminUsers {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function getAdminUsers() {
        try {
            $query = $this->db->prepare("SELECT nom, email FROM utilisateur WHERE role_id = 2");
            if (!$query) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }
            
            if (!$query->execute()) {
                throw new Exception("Query execution failed: " . $query->error);
            }
            
            $result = $query->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching admin users: " . $e->getMessage());
            return [];
        }
    }
}

// Keep your existing Login and Register classes here...
// [Previous Login and Register class code remains unchanged]

session_start();

// Initialize AdminUsers class
$adminUsers = new AdminUsers($db);
$admins = $adminUsers->getAdminUsers();

// Keep your existing POST handling code...
// [Previous POST handling code remains unchanged]
?>