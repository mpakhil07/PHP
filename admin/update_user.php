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
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'user';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Validate
    if($user_id <= 0 || empty($name) || empty($email)) {
        $message = "Invalid input data";
        $message_type = "danger";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format";
        $message_type = "danger";
    } elseif($password && strlen($password) < 6) {
        $message = "Password must be at least 6 characters";
        $message_type = "danger";
    } elseif(!in_array($role, ['user', 'admin'])) {
        $message = "Invalid role selected";
        $message_type = "danger";
    } else {
        try {
            // Check if email already exists for another user
            $check_email = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $check_email->execute([$email, $user_id]);
            
            if($check_email->rowCount() > 0) {
                $message = "Email already exists for another user";
                $message_type = "danger";
            } else {
                // Prepare update query
                if(!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE users SET name = :name, email = :email, role = :role, password = :password WHERE id = :id";
                } else {
                    $query = "UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id";
                }
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":role", $role);
                $stmt->bindParam(":id", $user_id);
                
                if(!empty($password)) {
                    $stmt->bindParam(":password", $hashed_password);
                }
                
                if($stmt->execute()) {
                    $message = "User updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to update user";
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