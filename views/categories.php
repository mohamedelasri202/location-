<?php
// Include the database connection file
include '../config/databasecnx.php';

// Define the Category class
class Category {
    private $db;
    private $name;
    private $description;

    // Constructor to initialize the database connection
    public function __construct($db) {
        $this->db = $db;
    }

    // Set the data for the category
    public function setData($name, $description) {
        $this->name = $name;
        $this->description = $description;
    }

    // Save the category to the database
    public function save() {
        // Debug: print the category name to verify
        var_dump($this->name);  // Check the inserted category name

        // Check if the category name already exists
        $checkQuery = "SELECT id FROM categorie WHERE nom = ?";
        $checkStmt = $this->db->prepare($checkQuery);
    
        if ($checkStmt) {
            $checkStmt->bind_param("s", $this->name);
            $checkStmt->execute();
            $checkStmt->store_result();
    
            if ($checkStmt->num_rows > 0) {
                // Category name already exists
                return "duplicate";
            }
        }
    
        // Insert the new category
        $query = "INSERT INTO categorie (nom, description) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
    
        if ($stmt) {
            $stmt->bind_param("ss", $this->name, $this->description);
            return $stmt->execute() ? "success" : "error";
        } else {
            return "error";
        }
    }
    // Function to fetch and display all categories
public function fetchAll() {
    $query = "SELECT * FROM categorie";
    $stmt = $this->db->prepare($query);
    
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        
        return $categories;
    } else {
        return [];
    }
}
// Update the category in the database
public function update($id, $name, $description) {
    $query = "UPDATE categorie SET nom = ?, description = ? WHERE id = ?";
    $stmt = $this->db->prepare($query);

    if ($stmt) {
        $stmt->bind_param("ssi", $name, $description, $id);
        if ($stmt->execute()) {
            return "success";
        } else {
            error_log("Update Error: " . $stmt->error); // Log any errors
            return "error";
        }
    } else {
        error_log("Prepare Statement Error: " . $this->db->error);
        return "error";
    }
}


// Delete the category from the database
public function delete($id) {
    $query = "DELETE FROM categorie WHERE id = ?";
    $stmt = $this->db->prepare($query);

    if ($stmt) {
        $stmt->bind_param("i", $id);
        return $stmt->execute() ? "success" : "error";
    } else {
        return "error";
    }
}


}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new ConnectData())->getConnection();
    $category = new Category($db);

    // Handle create
    if (!empty($_POST['categoryName']) && !empty($_POST['categoryDesc']) && !isset($_POST['editCategoryId']) && !isset($_POST['deleteCategoryId'])) {
        $category->setData($_POST['categoryName'], $_POST['categoryDesc']);
        $result = $category->save();
        // Handle result...
    }
    
    // Handle update
    if (isset($_POST['updateCategory'])) {
        $categoryId = $_POST['editCategoryId'] ?? null;
        $categoryName = $_POST['editCategoryName'] ?? null;
        $categoryDesc = $_POST['editCategoryDesc'] ?? null;
    
        if ($categoryId && $categoryName && $categoryDesc) {
            $result = $category->update($categoryId, $categoryName, $categoryDesc);
            if ($result === "success") {
                echo "Category updated successfully.";
            } else {
                echo "Error updating category.";
            }
        } else {
            echo "All fields are required.";
        }
    }
    
    
    
    // Handle delete
    if (isset($_POST['deleteCategoryId'])) {
        $result = $category->delete($_POST['deleteCategoryId']);
        // Handle result...
    }
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
                        <li class="text-[#363949]"><a href="listContrat.php" class="active">Categories &npr;</a></li>/
                        <li class="text-[#363949]"><a href="statistic.php">Statistic &npr;</a></li>
                    </ul>
                </div>
                <a id="buttonadd" href="#" class="report h-[36px] px-[16px] rounded-[36px] bg-[#1976D2] text-[#f6f6f6] flex items-center justify-center gap-[10px] font-medium">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add Category</span>
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
        <?php
        $categories = (new Category($db))->fetchAll();
        foreach ($categories as $category) {
            echo "<tr data-id='" . htmlspecialchars($category['id']) . "'>";
            echo "<td class='py-4 px-3'>" . htmlspecialchars($category['nom']) . "</td>";
            echo "<td class='py-4 px-3'>" . htmlspecialchars($category['description']) . "</td>";
            echo "<td class='py-4 px-3'>";
            echo "<a href='#' class='edit-btn'><i class='bx bx-edit-alt'></i></a>";
            echo "<a href='#' class='delete-btn'><i class='fa-solid fa-trash'></i></a>";
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
<form method="POST" style="display:none;" id="deleteForm">
    <input type="hidden" name="deleteCategoryId" id="deleteCategoryId">
</form>

                </div>
            </div>
        </main>
    </div>

    <!-- Add Category Form -->
    <div id="addClientForm" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[300px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px]">
        <form action="#" method="POST" class="flex flex-col gap-4">
            <h2 class="text-2xl font-semibold mb-5">Add Category</h2>
            <div class="form-group flex flex-col">
                <label for="categoryName" class="text-sm text-gray-700 mb-1">Category Name</label>
                <input name="categoryName" type="text" id="categoryName" placeholder="Enter category name" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
            </div>
            <div class="form-group flex flex-col">
                <label for="categoryDesc" class="text-sm text-gray-700 mb-1">Category Description</label>
                <textarea name="categoryDesc" id="categoryDesc" placeholder="Enter category description" class="p-2 border border-gray-300 rounded-lg outline-none text-sm resize-none h-[100px]"></textarea>
            </div>
            <button type="submit" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Add Category</button>
            <button type="button" id="closeForm" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Close</button>
        </form>
    </div>

    <!-- Edit Category Form -->
    <div id="editform" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[300px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px]">
    <form action="#" method="POST" class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold mb-5">Edit Category</h2>
        <input type="hidden" id="editCategoryId" name="editCategoryId">
        <div class="form-group flex flex-col">
            <label for="editCategoryName" class="text-sm text-gray-700 mb-1">Category Name</label>
            <input name="editCategoryName" type="text" id="editCategoryName" placeholder="Enter category name" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <div class="form-group flex flex-col">
            <label for="editCategoryDesc" class="text-sm text-gray-700 mb-1">Category Description</label>
            <textarea name="editCategoryDesc" id="editCategoryDesc" placeholder="Enter category description" class="p-2 border border-gray-300 rounded-lg outline-none text-sm resize-none h-[100px]"></textarea>
        </div>
        <button type="submit" name="updateCategory" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Update Category</button>
        <button type="button" id="colseedit" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Close</button>
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


document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const row = this.closest('tr');
        document.getElementById('categoryId').value = row.dataset.id;
        document.getElementById('categoryName').value = row.cells[0].textContent.trim();
        document.getElementById('categoryDesc').value = row.cells[1].textContent.trim();
        document.getElementById('addClientForm').style.right = '0';
    });
});

document.getElementById('closeForm').addEventListener('click', function () {
    document.getElementById('addClientForm').style.right = '-100%';
});


document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const row = this.closest('tr');
        document.getElementById('editCategoryId').value = row.dataset.id;
        document.getElementById('editCategoryName').value = row.cells[0].textContent.trim();
        document.getElementById('editCategoryDesc').value = row.cells[1].textContent.trim();
        document.getElementById('editform').style.right = '0';
    });
});

document.getElementById('colseedit').addEventListener('click', function () {
    document.getElementById('editform').style.right = '-100%';
});

document.querySelectorAll('.delete-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const row = this.closest('tr');
        const categoryId = row.dataset.id;
        if (confirm("Are you sure you want to delete this category?")) {
            document.getElementById('deleteCategoryId').value = categoryId;
            document.getElementById('deleteForm').submit();
        }
    });
});


    </script>
</body>
</html>