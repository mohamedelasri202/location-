<?php
// process-article.php
session_start();
require_once '../config/databasecnx.php';


class ArticleManager {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function addArticle($titre, $contenu, $theme_id, $utilisateur_id, $image = null) {
        $stmt = $this->db->prepare("INSERT INTO Article (titre, contenu, theme_id, utilisateur_id, statut) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->bind_param("ssii", $titre, $contenu, $theme_id, $utilisateur_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error adding article: " . $stmt->error);
        }
        
        return $this->db->insert_id;
    }
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
        $articleManager = new ArticleManager($db);
        
        // Validate input
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);
        $theme_id = (int)$_POST['theme_id'];
        $utilisateur_id = $_SESSION['user_id']; 
        
        if (empty($titre) || empty($contenu) || $theme_id <= 0) {
            throw new Exception("Please fill all required fields");
        }
        
        // Handle image upload if needed
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid image format");
            }
            
            $image_path = '../uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
        
        // Add article
        $article_id = $articleManager->addArticle($titre, $contenu, $theme_id, $utilisateur_id, $image_path);
        
        // Process tags
        
        
        // Redirect to success page
      
        
    } 
}
function getThemes($db) {
    $themes = array();
    $query = "SELECT id, theme_name FROM Theme ORDER BY theme_name ASC";
    $result = $db->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $themes[] = $row;
        }
    }
    return $themes;
}

// Get database connection using your existing class
$db = (new ConnectData())->getConnection();

// Fetch themes
$themes = getThemes($db);



$message = '';  // Variable to store success/error messages



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $articleManager = new ArticleManager($db);
        
        // Validate input
        $titre = trim($_POST['titre']);
        $contenu = trim($_POST['contenu']);
        $theme_id = (int)$_POST['theme_id'];
        $utilisateur_id = $_SESSION['user_id'];
        
        if (empty($titre) || empty($contenu) || $theme_id <= 0) {
            throw new Exception("Please fill all required fields");
        }
        
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (!in_array($ext, $allowed)) {
                throw new Exception("Invalid image format");
            }
            
            $image_path = 'uploads/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
        
        // Add article
        $article_id = $articleManager->addArticle($titre, $contenu, $theme_id, $utilisateur_id, $image_path);
        
        // Process tags
        if (isset($_POST['tag_list']) && is_array($_POST['tag_list'])) {
            $articleManager->addTags($article_id, $_POST['tag_list']);
        }
        
        $message = "Article successfully created!";
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch themes
$themes = getThemes($db);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add New Article</title>
    <style>
        .article-form {
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #4a90e2;
            outline: none;
            box-shadow: 0 0 0 2px rgba(74, 144, 226, 0.2);
        }

        textarea.form-control {
            min-height: 200px;
            resize: vertical;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 35px;
        }

        input[type="file"].form-control {
            padding: 8px;
            background: #f8f9fa;
        }

        .tag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
            min-height: 34px;
            padding: 5px;
            border: 1px dashed #ddd;
            border-radius: 4px;
        }

        .tag {
            background: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        .tag:hover {
            background: #dee2e6;
        }

        .tag button {
            background: none;
            border: none;
            color: #dc3545;
            margin-left: 6px;
            padding: 0 4px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }

        .btn-submit {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .btn-submit:hover {
            background: #357abd;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .article-form {
                margin: 10px;
                padding: 20px;
            }
            
            .form-control {
                padding: 10px;
                font-size: 14px;
            }
            
            .btn-submit {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form class="article-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titre">Title:</label>
                <input type="text" id="titre" name="titre" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="contenu">Content:</label>
                <textarea id="contenu" name="contenu" class="form-control" rows="10" required></textarea>
            </div>

            <div class="form-group">
                <label for="theme">Theme:</label>
                <select id="theme" name="theme_id" class="form-control" required>
                    <option value="">Select a theme</option>
                    <?php foreach ($themes as $theme): ?>
                        <option value="<?php echo htmlspecialchars($theme['id']); ?>">
                            <?php echo htmlspecialchars($theme['theme_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="image">Featured Image:</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma-separated):</label>
                <input type="text" id="tags" name="tags" class="form-control" placeholder="Enter tags and press comma">
                <div id="tagContainer" class="tag-container"></div>
            </div>

            <button type="submit" class="btn-submit">Publish Article</button>
        </form>
    </div>

    <script>
        const tagInput = document.getElementById('tags');
        const tagContainer = document.getElementById('tagContainer');
        let tags = [];

        tagInput.addEventListener('keyup', function(e) {
            if (e.key === ',') {
                const tagText = this.value.slice(0, -1).trim();
                if (tagText && !tags.includes(tagText)) {
                    tags.push(tagText);
                    updateTags();
                }
                this.value = '';
            }
        });

        function updateTags() {
            tagContainer.innerHTML = '';
            tags.forEach((tag, index) => {
                const tagElement = document.createElement('span');
                tagElement.className = 'tag';
                tagElement.innerHTML = `
                    ${tag}
                    <button type="button" onclick="removeTag(${index})">&times;</button>
                    <input type="hidden" name="tag_list[]" value="${tag}">
                `;
                tagContainer.appendChild(tagElement);
            });
        }

        function removeTag(index) {
            tags.splice(index, 1);
            updateTags();
        }
    </script>
</body>
</html>
    

       
