<?php
require_once __DIR__ . '/../config/databasecnx.php';

class Vehicle {
    private $db;
    private $marque;
    private $modele;
    private $categorie_id;
    private $prix_journalier;
    private $disponibilite;
    private $description;
    private $image_url;

    public function __construct($db) {
        $this->db = $db;
    }

    public function setData($marque, $modele, $categorie_id, $prix_journalier, $disponibilite, $description, $image_url) {
        $this->marque = $marque;
        $this->modele = $modele;
        $this->categorie_id = $categorie_id;
        $this->prix_journalier = $prix_journalier;
        $this->disponibilite = $disponibilite;
        $this->description = $description;
        $this->image_url = $image_url;
    }

    public function getCategories() {
        $query = "SELECT id, nom FROM categorie ORDER BY nom";
        $result = $this->db->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function save() {
        $query = "INSERT INTO vehicule (marque, modele, categorie_id, prix_journalier, disponibilite, description, image_url) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("ssidiss", 
                $this->marque, 
                $this->modele, 
                $this->categorie_id,
                $this->prix_journalier, 
                $this->disponibilite, 
                $this->description, 
                $this->image_url
            );
            
            try {
                $result = $stmt->execute();
                $stmt->close();
                return $result ? "success" : "error: " . $this->db->error;
            } catch (Exception $e) {
                return "error: " . $e->getMessage();
            }
        }
        return "error: " . $this->db->error;
    }

    public function fetchAll() {
        $query = "SELECT v.*, c.nom as nom_categorie 
                 FROM vehicule v 
                 LEFT JOIN categorie c ON v.categorie_id = c.id 
                 ORDER BY v.id DESC";
        $result = $this->db->query($query);
        
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function getVehicleById($id) {
        try {
            $query = "SELECT v.*, c.nom as nom_categorie 
                     FROM vehicule v 
                     LEFT JOIN categorie c ON v.categorie_id = c.id 
                     WHERE v.id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error fetching vehicle: " . $e->getMessage());
            return null;
        }
    }

    public function updateAvailability($id, $availability) {
        try {
            $query = "UPDATE vehicule SET disponibilite = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("ii", $availability, $id);
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating availability: " . $e->getMessage());
            return false;
        }
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $db = (new ConnectData())->getConnection();
        
        if (!$db) {
            throw new Exception("Database connection failed");
        }

        $vehicle = new Vehicle($db);
        $uploadDir = '../uploads/';
        $errors = [];
        $success = [];

        // Create uploads directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Loop through each car
        $numCars = count($_POST['currentMarque'] ?? []);
        for ($i = 0; $i < $numCars; $i++) {
            // Validate required fields
            if (empty($_POST['currentMarque'][$i]) || empty($_POST['currentModele'][$i])) {
                $errors[] = "Marque and Modele are required for car #" . ($i + 1);
                continue;
            }

            // Handle image upload
            $imagePath = null;
            if (isset($_FILES['currentImageFile']['name'][$i]) && $_FILES['currentImageFile']['error'][$i] === 0) {
                $fileName = time() . '_' . basename($_FILES['currentImageFile']['name'][$i]);
                $targetPath = $uploadDir . $fileName;
                
                // Validate image
                $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($imageFileType, $allowedTypes)) {
                    $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed for car #" . ($i + 1);
                    continue;
                }
                
                if (move_uploaded_file($_FILES['currentImageFile']['tmp_name'][$i], $targetPath)) {
                    $imagePath = 'uploads/' . $fileName;
                } else {
                    $errors[] = "Failed to upload image for car #" . ($i + 1);
                    continue;
                }
            }

            // Set and save vehicle data
            $vehicle->setData(
                $_POST['currentMarque'][$i],
                $_POST['currentModele'][$i],
                $_POST['currentCategory'][$i],
                $_POST['currentPrixJournalier'][$i],
                $_POST['currentDisponibilite'][$i],
                $_POST['currentDescription'][$i],
                $imagePath
            );

            $result = $vehicle->save();
            if ($result === "success") {
                $success[] = "Vehicle #" . ($i + 1) . " added successfully";
            } else {
                $errors[] = "Failed to add vehicle #" . ($i + 1) . ": " . $result;
            }
        }

        // Return JSON response
        echo json_encode([
            'status' => empty($errors) ? 'success' : 'error',
            'errors' => $errors,
            'success' => $success
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'errors' => ["System error: " . $e->getMessage()],
            'success' => []
        ]);
    }
    exit;
}

// Get categories for the form
$db = (new ConnectData())->getConnection();
$vehicle = new Vehicle($db);
$categories = $vehicle->getCategories();
?>
