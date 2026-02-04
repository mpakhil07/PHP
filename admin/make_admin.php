<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$message = '';
$message_type = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    
    if($user_id <= 0) {
        $message = "Invalid user ID";
        $message_type = "danger";
    } elseif($user_id == $_SESSION['admin_id']) {
        $message = "You are already an admin!";
        $message_type = "warning";
    } else {
        try {
            // Check if user exists
            $check_user = $db->prepare("SELECT name, email FROM users WHERE id = ?");
            $check_user->execute([$user_id]);
            
            if($check_user->rowCount() == 0) {
                $message = "User not found";
                $message_type = "danger";
            } else {
                $user = $check_user->fetch();
                
                // Update user role
                $query = "UPDATE users SET role = 'admin' WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":id", $user_id);
                
                if($stmt->execute()) {
                    $message = "User '{$user['name']}' ({$user['email']}) has been promoted to administrator!";
                    $message_type = "success";
                } else {
                    $message = "Failed to promote user";
                    $message_type = "danger";
                }
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $message_type = "danger";
        }
    }
} else {
    $message = "Invalid request method";
    $message_type = "danger";
}

// Store message in session
$_SESSION['message'] = $message;
$_SESSION['message_type'] = $message_type;

// Redirect back to users page
header("Location: users.php");
exit();
?>