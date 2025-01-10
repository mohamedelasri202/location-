<?php
// auth.php
// function checkAuth($requiredRole = null) {
//     if (session_status() === PHP_SESSION_NONE) {
//         session_start();
//     }
    
//     // Check if user is logged in
//     if (!isset($_SESSION['user_id'])) {
//         header("Location: /path/to/your/Classes/signin.php");
//         exit;
//     }
    
//     If a specific role is required, check for it
//     if ($requiredRole !== null && $_SESSION['role_id'] != $requiredRole) {
//         if ($_SESSION['role_id'] == 1) {
//             header("Location: views/listCars.php");
//         } else {
//             header("Location:index.php");
//         }
//         exit;
//     }
    
//     return true;
// }

// function isAdmin() {
//     return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1;
// }

// function isUser() {
//     return isset($_SESSION['role_id']) && $_SESSION['role_id'] == 2;
// }