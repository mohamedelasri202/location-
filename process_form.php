<?php
require_once('config/databasecnx.php'); 
require_once ('ikhan.php'); // Make sure this file connects to your database

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

