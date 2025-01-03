<?php
include '../config/databasecnx.php';

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






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href=".././assets/style.css">
    <script src=".././assets/tailwind.js"></script>
</head>

<body class="">
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
            <li class="h-12 bg-transparent ml-1.5 rounded-l-full p-1"><a href="statistic.php" class="menu-item"><i class="fa-solid fa-chart-simple"></i>Statistic</a></li>
        </ul>
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
                    <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search...">
                    <button class="w-[80px] h-full flex justify-center items-center bg-[#1976D2] text-[#f6f6f9] text-[18px] border-0 outline-none rounded-r-[36px] cursor-pointer" type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="theme-toggle" hidden>
            <label for="theme-toggle" class="theme-toggle block min-w-[50px] h-[25px] bg-grey cursor-pointer relative rounded-full"></label>
            <a href="#" class="notif text-[20px] relative">
                <i class='bx bx-bell'></i>
                <span class="count absolute top-[-6px] right-[-6px] w-[20px] h-[20px] bg-[#D32F2F] text-[#f6f6f6] border-2 border-[#f6f6f9] font-semibold text-[12px] flex items-center justify-center rounded-full">12</span>
            </a>
            <a href="#" class="profile">
                <img class="w-[36px] h-[36px] object-cover rounded-full" width="36" height="36" src=".././assets/image/1054-1728555216-removebg-preview.png">
            </a>
        </nav>

        <!-- Main Content -->
        <main class="mainn w-full p-[36px_24px] max-h-[calc(100vh_-_56px)]">
            <div class="header flex items-center justify-between gap-[16px] flex-wrap">
                <div class="left">
                    <ul class="breadcrumb flex items-center space-x-[16px]">
                        <li class="text-[#363949]"><a href="listClients.php">Client &npr;</a></li>/ 
                        <li class="text-[#363949]"><a href="listCars.php">Cars &npr;</a></li>/ 
                        <li class="text-[#363949]"><a href="categories.php" class="active">Categories &npr;</a></li>/ 
                        <li class="text-[#363949]"><a href="statistic.php">Statistic &npr;</a></li>
                    </ul>
                </div>
                <a id="buttonadd" href="#" class="report h-[36px] px-[16px] rounded-[36px] bg-[#1976D2] text-[#f6f6f6] flex items-center justify-center gap-[10px] font-medium">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Car</span>
                </a>
            </div>

            <!-- Insights -->
            <ul class="insights grid grid-cols-[repeat(auto-fit,_minmax(240px,_1fr))] gap-[24px] mt-[36px]">
                <li>
                    <i class="fa-solid fa-user-group"></i>
                    <span class="info">
                        <h3>150</h3>
                        <p>Clients</p>
                    </span>
                </li>
                <li>
                    <i class="fa-solid fa-car-side"></i>
                    <span class="info">
                        <h3>75</h3>
                        <p>Cars</p>
                    </span>
                </li>
                <li>
                    <i class="fa-solid fa-file-signature"></i>
                    <span class="info">
                        <h3>45</h3>
                        <p>Categories</p>
                    </span>
                </li>
            </ul>

            <!-- Data Content -->
            <div class="bottom-data flex flex-wrap gap-[24px] mt-[24px] w-full">
                <div class="orders flex-grow flex-[1_0_500px]">
                    <div class="header flex items-center gap-[16px] mb-[24px]">
                        <i class='bx bx-list-check'></i>
                        <h3 class="mr-auto text-[24px] font-semibold">List Categories</h3>
                        <i class='bx bx-filter'></i>
                        <i class='bx bx-search'></i>
                    </div>

                    <!-- Table -->
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="pb-3 px-3 text-sm text-left border-b border-grey">Name</th>
                                <th class="pb-3 px-3 text-sm text-left border-b border-grey">Description</th>
                                <th class="pb-3 px-5 text-sm text-left border-b border-grey">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="py-4 px-3">Luxury Cars</td>
                                <td class="py-4 px-3">High-end vehicles with premium features</td>
                                <td class="py-4 px-3 edit-button">
                                    <a href="#" class="edit-btn"><i class='bx bx-edit-alt'></i></a>
                                    <a href="#"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Car Form -->
    <div id="addClientForm" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[300px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px]">
    <form id="carsForm" action="#" method="POST" class="flex flex-col gap-4" enctype="multipart/form-data">
        <h2 class="text-2xl font-semibold mb-5">Add Multiple Cars</h2>
        <div id="alerts"></div>
        <div id="carsContainer">
            <div class="car-input">
                <i class="fas fa-times remove-car" onclick="removeCar(this)"></i>
                <h3 class="text-xl font-semibold">Car 1</h3>
                
                <div class="form-group flex flex-col">
                    <label for="currentMarque0" class="text-sm text-gray-700 mb-1">Car Make *</label>
                    <input type="text" name="currentMarque[]" id="currentMarque0" required placeholder="Enter car make" 
                           class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentModele0" class="text-sm text-gray-700 mb-1">Car Model *</label>
                    <input type="text" name="currentModele[]" id="currentModele0" required placeholder="Enter car model"
                           class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentCategory0" class="text-sm text-gray-700 mb-1">Car Category</label>
                    <select name="currentCategory[]" id="currentCategory0" 
                            class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                <?php echo htmlspecialchars($category['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentPrixJournalier0" class="text-sm text-gray-700 mb-1">Daily Price *</label>
                    <input type="number" name="currentPrixJournalier[]" id="currentPrixJournalier0" required min="0" step="0.01"
                           placeholder="Enter daily rental price" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentDisponibilite0" class="text-sm text-gray-700 mb-1">Availability</label>
                    <select name="currentDisponibilite[]" id="currentDisponibilite0" 
                            class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                        <option value="1">Available</option>
                        <option value="0">Not Available</option>
                    </select>
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentDescription0" class="text-sm text-gray-700 mb-1">Description</label>
                    <textarea name="currentDescription[]" id="currentDescription0" rows="3" 
                              placeholder="Enter car description" 
                              class="p-2 border border-gray-300 rounded-lg outline-none text-sm resize-none h-[100px]"></textarea>
                </div>

                <div class="form-group flex flex-col">
                    <label for="currentImageFile0" class="text-sm text-gray-700 mb-1">Car Image</label>
                    <input type="file" name="currentImageFile[]" id="currentImageFile0" accept="image/*" 
                           onchange="previewImage(this)" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
                    <div class="image-preview"></div>
                </div>
            </div>
        </div>

        <button type="button" class="btn add-btn submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out" onclick="addCar()">
            <i class="fas fa-plus"></i> Add Another Car
        </button>
        <button type="submit" class="btn submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">
            <i class="fas fa-save"></i> Save All Cars
        </button>
        <button type="button" id="closeForm" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Close</button>
    </form>
</div>

    <script >



document.getElementById('buttonadd').addEventListener('click', function(e) {
    e.preventDefault()
    document.getElementById('addClientForm').classList.add('active');
});
document.getElementById('closeForm').addEventListener('click', function() {
        document.getElementById('addClientForm').classList.remove('active');
    })




let carCount = 1;

function addCar() {
    const container = document.getElementById('carsContainer');
    const newCar = document.querySelector('.car-input').cloneNode(true);
    
    // Update car number in the heading
    newCar.querySelector('h3').textContent = `Car ${++carCount}`;
    
    // Reset all inputs
    newCar.querySelectorAll('input, textarea, select').forEach(input => {
        input.value = '';
        // Update IDs to maintain uniqueness
        if (input.id) {
            input.id = input.id.replace(/\d+/, carCount - 1);
        }
    });
    
    // Reset image preview
    const imagePreview = newCar.querySelector('.image-preview');
    if (imagePreview) {
        imagePreview.innerHTML = '';
    }
    
    container.appendChild(newCar);
}

function removeCar(element) {
    if (document.querySelectorAll('.car-input').length > 1) {
        element.closest('.car-input').remove();
        updateCarNumbers();
    } else {
        const alertsDiv = document.getElementById('alerts');
        alertsDiv.innerHTML = '<div class="alert alert-error p-4 mb-4 text-red-700 bg-red-100 rounded-lg">You must have at least one car</div>';
    }
}

function updateCarNumbers() {
    document.querySelectorAll('.car-input h3').forEach((header, index) => {
        header.textContent = `Car ${index + 1}`;
    });
    carCount = document.querySelectorAll('.car-input').length;
}

function previewImage(input) {
    const preview = input.parentElement.querySelector('.image-preview');
    preview.innerHTML = '';
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'preview-image w-full h-32 object-cover rounded-lg mt-2';
            preview.appendChild(img);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Form submission handler
document.getElementById('carsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const alertsDiv = document.getElementById('alerts');
        alertsDiv.innerHTML = '';

        if (data.errors && data.errors.length > 0) {
            data.errors.forEach(error => {
                alertsDiv.innerHTML += `<div class="alert alert-error">${error}</div>`;
            });
        }

        if (data.success && data.success.length > 0) {
            data.success.forEach(message => {
                alertsDiv.innerHTML += `<div class="alert alert-success">${message}</div>`;
            });
            if (data.status === 'success') {
                setTimeout(() => {
                    window.location.href = 'list_cars.php';
                }, 2000);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('alerts').innerHTML = 
            '<div class="alert alert-error">An error occurred while saving the cars</div>';
    });
});

// Add slide-out form functionality
document.addEventListener('DOMContentLoaded', function() {
    const addButton = document.querySelector('[data-target="addClientForm"]');
    const closeButton = document.getElementById('closeForm');
    const form = document.getElementById('addClientForm');

    if (addButton) {
        addButton.addEventListener('click', () => {
            form.style.right = '0';
        });
    }

    if (closeButton) {
        closeButton.addEventListener('click', () => {
            form.style.right = '-100%';
        });
    }
});
    </script>

    
</body>
</html>