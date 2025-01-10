<?php
require_once(__DIR__ . '/../config/databasecnx.php');

class Login {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function authenticateUser($username, $password) {
        try {
            $query = $this->db->prepare("SELECT id, mot_de_passe, role_id FROM utilisateur WHERE nom = ?");
            if (!$query) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $query->bind_param("s", $username);
            if (!$query->execute()) {
                throw new Exception("Query execution failed: " . $query->error);
            }

            $result = $query->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['mot_de_passe'])) {
                    // Start session if not already started
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    // Regenerate session ID for security
                    session_regenerate_id(true);

                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $username;
                    $_SESSION['role_id'] = $user['role_id'];
                    return $user['role_id'];
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Authentication error: " . $e->getMessage());
            return false;
        }
    }
}

class Register {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function checkIfUserExists($username, $email) {
        $query = $this->db->prepare("SELECT id FROM utilisateur WHERE nom = ? OR email = ?");
        $query->bind_param("ss", $username, $email);
        $query->execute();
        $result = $query->get_result();
        return $result->num_rows > 0;
    }

    public function registerUser($username, $email, $password) {
        try {
            // Check if user already exists
            if ($this->checkIfUserExists($username, $email)) {
                throw new Exception("Username or email already exists");
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Default role ID for regular users
            $role_id = 2;
            
            $query = $this->db->prepare("INSERT INTO utilisateur (nom, email, mot_de_passe, role_id, date_inscription) 
                                       VALUES (?, ?, ?, ?, NOW())");
            if (!$query) {
                throw new Exception("Query preparation failed: " . $this->db->error);
            }

            $query->bind_param("sssi", $username, $email, $hashedPassword, $role_id);
            
            if ($query->execute()) {
                return true;
            } else {
                throw new Exception("Failed to register user");
            }
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            throw $e;
        }
    }
}

// Handle Sign In
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign-in-submit'])) {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        if (empty($username) || empty($password)) {
            echo "<script>alert('All login fields are required!');</script>";
            exit;
        }

        $login = new Login($db);
        $roleId = $login->authenticateUser($username, $password);

        if ($roleId) {
            ob_clean();
            if ($roleId == 1) {
                header("Location: ../views/listCars.php");
            } else {
                header("Location: ../index.php");
            }
            exit;
        } else {
            echo "<script>alert('Invalid username or password!');</script>";
        }
    }
}

// Handle Sign Up
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sign-up-submit'])) {
    if (isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            echo "<script>alert('All registration fields are required!');</script>";
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo "<script>alert('Please enter a valid email address!');</script>";
            exit;
        }

        try {
            $register = new Register($db);
            if ($register->registerUser($username, $email, $password)) {
                echo "<script>
                    alert('Registration successful! Please login.');
                    window.location.href = window.location.href;
                </script>";
                exit;
            }
        } catch (Exception $e) {
            echo "<script>alert('Registration failed: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<!-- Your existing HTML code remains exactly the same -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href=".././assets/stylesign.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Sign in / Sign up Form</title>
</head>
<body>
    <div class="container">
        <div class="forms-container">
            <div class="signin-signup">
                <!-- Sign In Form -->
                <form method="post" action="" class="sign-in-form">
                    <h2 class="title">Sign in</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input name="username" type="text" placeholder="Username" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input name="password" type="password" placeholder="Password" required />
                    </div>
                    <input type="submit" name="sign-in-submit" value="Login" class="btn solid" />
                </form>

                <!-- Sign Up Form -->
                <form method="post" action="" class="sign-up-form">
                    <h2 class="title">Sign up</h2>
                    <div class="input-field">
                        <i class="fas fa-user"></i>
                        <input name="username" type="text" placeholder="Username" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-envelope"></i>
                        <input name="email" type="email" placeholder="Email" required />
                    </div>
                    <div class="input-field">
                        <i class="fas fa-lock"></i>
                        <input name="password" type="password" placeholder="Password" required />
                    </div>
                    <input type="submit" name="sign-up-submit" class="btn" value="Sign up" />
                </form>
            </div>
        </div>

        <div class="panels-container">
            <div class="panel left-panel">
                <div class="content">
                    <a href="" class="logo text-[50px] font-bold h-[56px] flex items-center text-[#1976D2] z-30 pb-[20px] box-content">
                        <i class="mt-4 text-xxl max-w-[60px] flex justify-center"><i class="fa-solid fa-car-side"></i></i>
                        <div class="logoname ml-2"><span class="text-black">Loca</span>Auto</div>
                    </a>
                    <button class="btn transparent" id="sign-up-btn">Sign up</button>
                </div>
            </div>
            <div class="panel right-panel">
                <div class="content">
                    <a href="" class="logo text-[50px] font-bold h-[56px] flex items-center text-[#1976D2] z-30 pb-[20px] box-content">
                        <i class="mt-4 text-xxl max-w-[60px] flex justify-center"><i class="fa-solid fa-car-side"></i></i>
                        <div class="logoname ml-2"><span class="text-black">Loca</span>Auto</div>
                    </a>
                    <button class="btn transparent" id="sign-in-btn">Sign in</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sign_in_btn = document.querySelector("#sign-in-btn");
        const sign_up_btn = document.querySelector("#sign-up-btn");
        const container = document.querySelector(".container");

        sign_up_btn.addEventListener("click", () => {
            container.classList.add("sign-up-mode");
        });

        sign_in_btn.addEventListener("click", () => {
            container.classList.remove("sign-up-mode");
        });

        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.pushState(null, null, location.href);
        };
    </script>
</body>
</html>
