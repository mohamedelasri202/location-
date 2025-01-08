<?php
require_once '../config/databasecnx.php';

class Theme {
    private $db;
    private $theme_name;
    private $theme_description;

    public function __construct($db) {
        $this->db = $db;
    }

    public function setData($theme_name, $theme_description) {
        // Fix: Remove the arrow operator, use assignment operator
        $this->theme_name = $theme_name;
        $this->theme_description = $theme_description;
    }

    public function save() {
        try {
            // Check if theme already exists
            $checkQuery = "SELECT id FROM theme WHERE theme_name = ?";
            $checkStmt = $this->db->prepare($checkQuery);

            if (!$checkStmt) {
                error_log("Prepare Statement Error: " . $this->db->error);
                return "error";
            }

            $checkStmt->bind_param("s", $this->theme_name);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                return "duplicate";
            }

            // Insert new theme
            $query = "INSERT INTO theme (theme_name, theme_description) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);

            if (!$stmt) {
                error_log("Prepare Statement Error: " . $this->db->error);
                return "error";
            }

            $stmt->bind_param("ss", $this->theme_name, $this->theme_description);
            return $stmt->execute() ? "success" : "error";

        } catch (Exception $e) {
            error_log("Theme Save Error: " . $e->getMessage());
            return "error";
        }
    }

    // Add method to fetch all themes
    public function fetchAll() {
        try {
            $query = "SELECT * FROM theme";
            $stmt = $this->db->prepare($query);
            
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            return [];
        } catch (Exception $e) {
            error_log("Fetch Themes Error: " . $e->getMessage());
            return [];
        }
    }

    // Add update method
    public function update($id, $theme_name, $theme_description) {
        try {
            $query = "UPDATE theme SET theme_name = ?, theme_description = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);

            if (!$stmt) {
                error_log("Prepare Statement Error: " . $this->db->error);
                return "error";
            }

            $stmt->bind_param("ssi", $theme_name, $theme_description, $id);
            return $stmt->execute() ? "success" : "error";

        } catch (Exception $e) {
            error_log("Theme Update Error: " . $e->getMessage());
            return "error";
        }
    }

    // Add delete method
    public function delete($id) {
        try {
            $query = "DELETE FROM theme WHERE id = ?";
            $stmt = $this->db->prepare($query);

            if (!$stmt) {
                error_log("Prepare Statement Error: " . $this->db->error);
                return "error";
            }

            $stmt->bind_param("i", $id);
            return $stmt->execute() ? "success" : "error";

        } catch (Exception $e) {
            error_log("Theme Delete Error: " . $e->getMessage());
            return "error";
        }
    }
}

// Handle POST requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = (new ConnectData())->getConnection();
    $theme = new Theme($db);

    // Handle create
    if (!empty($_POST['theme_name']) && !empty($_POST['theme_description']) 
        && !isset($_POST['editThemeId']) && !isset($_POST['deleteThemeId'])) {
        
        $theme->setData($_POST['theme_name'], $_POST['theme_description']);
        $result = $theme->save();
        
        switch($result) {
            case "success":
                echo json_encode(["status" => "success", "message" => "Theme created successfully"]);
                break;
            case "duplicate":
                echo json_encode(["status" => "error", "message" => "Theme name already exists"]);
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Error creating theme"]);
        }
    }

    // Handle update
    if (isset($_POST['updateTheme'])) {
        $themeId = $_POST['editThemeId'] ?? null;
        $themeName = $_POST['editThemeName'] ?? null;
        $themeDescription = $_POST['editThemeDescription'] ?? null;

        if ($themeId && $themeName && $themeDescription) {
            $result = $theme->update($themeId, $themeName, $themeDescription);
            echo json_encode([
                "status" => $result === "success" ? "success" : "error",
                "message" => $result === "success" ? "Theme updated successfully" : "Error updating theme"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
        }
    }

    // Handle delete
    if (isset($_POST['deleteThemeId'])) {
        $result = $theme->delete($_POST['deleteThemeId']);
        echo json_encode([
            "status" => $result === "success" ? "success" : "error",
            "message" => $result === "success" ? "Theme deleted successfully" : "Error deleting theme"
        ]);
    }
}?>


























<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href=".././assets/style.css">
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
                <input class="flex-grow px-[16px] h-full border-0 bg-[#eee] rounded-l-[36px] outline-none w-full text-[#363949]" type="search" placeholder="Search themes...">
                <button class="w-[80px] h-full flex justify-center items-center bg-[#1976D2] text-[#f6f6f9] text-[18px] border-0 outline-none rounded-r-[36px] cursor-pointer" type="submit">
                    <i class='bx bx-search'></i>
                </button>
            </div>
        </form>
        <a href="#" class="profile">
            <img class="w-[36px] h-[36px] object-cover rounded-full" src=".././assets/image/admin-profile.png" alt="Profile">
        </a>
    </nav>

    <!-- Main Content -->
    <main class="mainn w-full p-[36px_24px] max-h-[calc(100vh_-_56px)]">
        <div class="header flex items-center justify-between gap-[16px] flex-wrap">
            <h1 class="text-2xl font-bold">Theme Management</h1>
            <a id="buttonadd" href="#" class="report h-[36px] px-[16px] rounded-[36px] bg-[#1976D2] text-[#f6f6f9] flex items-center justify-center gap-[10px] font-medium">
                <i class="fa-solid fa-plus"></i>
                <span>Add Theme</span>
            </a>
        </div>

        <!-- Table -->
        <div class="mt-8">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Theme Name</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Description</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Article Count</th>
                        <th class="pb-3 px-3 text-sm text-left border-b border-grey">Actions</th>
                    </tr>
                </thead>
                <?php
        $themes = (new Theme($db))->fetchAll();
        foreach ($themes as $theme) {
            echo "<tr data-id='" . htmlspecialchars($theme['id']) . "'>";
            echo "<td class='py-4 px-3'>" . htmlspecialchars($theme['theme_name']) . "</td>";
            echo "<td class='py-4 px-3'>" . htmlspecialchars($theme['theme_description']) . "</td>";
            echo "<td class='py-4 px-3'>";
            echo "<a href='#' class='edit-btn'><i class='bx bx-edit-alt'></i></a>";
            echo "<a href='#' class='delete-btn'><i class='fa-solid fa-trash'></i></a>";
            echo "</td>";
            echo "</tr>";
        }
        ?>
            </table>
        </div>
    </main>
</div>
<form method="POST" style="display:none;" id="deleteForm">
    <input type="hidden" name="deleteThemeId" id="deleteThemeId">
</form>

<!-- Add Theme Modal -->
<div id="addClientForm" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[350px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px] bg-white">
    <form action="#" method="post" class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold mb-5">Add New Theme</h2>
        <div class="form-group flex flex-col">
            <label for="themeName" class="text-sm text-gray-700 mb-1">Theme Name</label>
            <input name="theme_name" type="text" id="themeName" placeholder="Enter theme name" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <div class="form-group flex flex-col">
            <label for="themeDescription" class="text-sm text-gray-700 mb-1">Description</label>
            <textarea name="theme_description" id="themeDescription" placeholder="Enter theme description" class="p-2 border border-gray-300 rounded-lg outline-none text-sm" rows="3"></textarea>
        </div>
        <button type="submit" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-[#1976D2] text-white">Add Theme</button>
        <button type="button" id="closeForm" class="close-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out bg-gray-300">Close</button>
    </form>
</div>
<!-- edi -->
<div id="editform" class="add-client-form fixed right-[-100%] w-full max-w-[400px] h-[300px] shadow-[2px_0_10px_rgba(0,0,0,0.1)] p-6 flex flex-col gap-5 transition-all duration-700 ease-in-out z-50 top-[166px]">
    <form action="#" method="POST" class="flex flex-col gap-4">
        <h2 class="text-2xl font-semibold mb-5">Edit Category</h2>
        <input type="hidden" id="editThemeId" name="editThemeId">
        <div class="form-group flex flex-col">
            <label for="editThemeName" class="text-sm text-gray-700 mb-1">Category Name</label>
            <input name="editThemeName" type="text" id="editThemeName" placeholder="Enter category name" class="p-2 border border-gray-300 rounded-lg outline-none text-sm">
        </div>
        <div class="form-group flex flex-col">
            <label for="editThemeDescription" class="text-sm text-gray-700 mb-1">Category Description</label>
            <textarea name="editThemeDescription" id="editThemeDescription" placeholder="Enter category description" class="p-2 border border-gray-300 rounded-lg outline-none text-sm resize-none h-[100px]"></textarea>
        </div>
        <button type="submit" name="updateTheme" class="submit-btn border-none px-4 py-2 rounded-lg cursor-pointer transition-all duration-500 ease-in-out">Update Category</button>
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
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        const row = this.closest('tr');
        document.getElementById('editThemeId').value = row.dataset.id;
        document.getElementById('editThemeName').value = row.cells[0].textContent.trim();
        document.getElementById('editThemeDescription').value = row.cells[1].textContent.trim();
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
            document.getElementById('deleteThemeId').value = categoryId;
            document.getElementById('deleteForm').submit();
        }
    });
});
</script>
</body>
</html>