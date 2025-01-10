<?php
// Assuming your connection file is 'config/databasecnx.php'
require_once('config/databasecnx.php');

// Create the Post class using your existing database connection
class Post {
    private $db;
    private $id;
    private $titre;
    private $contenu;
    private $dateCreation;
    private $statut;
    private $utilisateur_id;
    private $theme_id;
    private $image_url;

    // Constructor to initialize database connection
    public function __construct($db) {
        $this->db = $db;
    }

    // Setters to assign values to the properties
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitre($titre) {
        $this->titre = $titre;
    }

    public function setContenu($contenu) {
        $this->contenu = $contenu;
    }

    public function setDateCreation($dateCreation) {
        $this->dateCreation = $dateCreation;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

    public function setUtilisateurId($utilisateur_id) {
        $this->utilisateur_id = $utilisateur_id;
    }

    public function setThemeId($theme_id) {
        $this->theme_id = $theme_id;
    }

    public function setImageUrl($image_url) {
        $this->image_url = $image_url;
    }

    // Method to insert data into the table
    public function insert() {
        try {
            // Prepare SQL query
            $query = $this->db->prepare("INSERT INTO article (titre, contenu, dateCreation, statut, utilisateur_id, theme_id, image_url) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?)");

            // Bind the parameters
            $query->bind_param("ssssiss", $this->titre, $this->contenu, $this->dateCreation, $this->statut, 
                               $this->utilisateur_id, $this->theme_id, $this->image_url);

            // Execute the query
            if ($query->execute()) {
                echo "Data inserted successfully!";
            } else {
                echo "Error: " . $query->error;
            }

            // Close the statement
            $query->close();

        } catch (Exception $e) {
            error_log("Error: " . $e->getMessage());
            echo "Failed to insert data.";
        }
    }
}

// Example usage

// Assuming your connection file is 'config/databasecnx.php'
require_once('config/databasecnx.php');  // Make sure this file connects to your database

// Create a new Post object with the existing database connection
$post = new Post($db); // Pass the existing connection

// Set values for the post (replace with actual form data)
$post->setTitre("Post Title");
$post->setContenu("This is the content of the post.");
$post->setDateCreation("2025-01-10");  // Example date
$post->setStatut("published");
$post->setUtilisateurId(1);  // Example user ID
$post->setThemeId(2);  // Example theme ID
$post->setImageUrl("uploads/example-image.jpg");  // Example image URL

// Insert the data into the database
$post->insert();

// Close the database connection
$db->close();
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Table Form</title>
</head>
<body>
    <h2>Fill Table Form</h2>
    <form action="process_form.php" method="post" enctype="multipart/form-data">
        <label for="titre">Title:</label>
        <input type="text" id="titre" name="titre" required><br><br>

        <label for="contenu">Content:</label><br>
        <textarea id="contenu" name="contenu" rows="5" cols="30" required></textarea><br><br>

        <label for="dateCreation">Date of Creation:</label>
        <input type="date" id="dateCreation" name="dateCreation" required><br><br>

        <label for="statut">Status:</label>
        <select id="statut" name="statut" required>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
            <option value="archived">Archived</option>
        </select><br><br>

        <label for="utilisateur_id">User ID:</label>
        <input type="number" id="utilisateur_id" name="utilisateur_id" required><br><br>

        <label for="theme_id">Theme ID:</label>
        <input type="number" id="theme_id" name="theme_id" required><br><br>

        <label for="image_url">Image:</label>
        <input type="file" id="image_url" name="image_url" accept="image/*"><br><br>

        <button type="submit">Submit</button>
    </form>
</body>
</html>
