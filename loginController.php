<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('Models/Database.php');

// Handle POST requests only (login or logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Handle logout
    if (isset($_POST['logoutbutton'])) {
        session_unset();  // Remove all session variables
        session_destroy(); // Destroy the session
        header('Location: ' . $_SERVER['PHP_SELF']); // Redirect back to current page
        exit();
    }

    // Handle login
    if (isset($_POST['loginbutton'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password)) {
            try {
                $db = Database::getInstance()->getConnection();

                // Fetch user by username and password
                // NOTE: In a real app, password should be hashed and verified using password_verify()
                $query = "SELECT * FROM ecoUser WHERE username = :username AND password = :password";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':username' => $username,
                    ':password' => $password
                ]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Successful login — store user session
                    $_SESSION['loggedin'] = true;
                    $_SESSION['userID'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['userType'] = $user['userType'];

                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    // Invalid login credentials
                    echo "<script>alert('Invalid username or password.');</script>";
                }

            } catch (PDOException $e) {
                // Handle database connection or query failure
                echo "<script>alert('Database error: " . $e->getMessage() . "');</script>";
            }

        } else {
            // Both fields are required
            echo "<script>alert('Please enter both username and password.');</script>";
        }
    }
}
?>
