<?php
session_start();
require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$error = '';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        $error = "Please enter email and password";
    } else {
        $query = "SELECT id, name, email, password, role FROM users WHERE email = :email AND role = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['admin_email'] = $row['email'];
                $_SESSION['admin_role'] = $row['role'];
                
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                $error = "Invalid password";
            }
        } else {
            $error = "No admin account found with this email";
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h3 class="text-center">
                    <i class="bi bi-shield-lock"></i> Admin Login
                </h3>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Admin Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock"></i> Admin Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="bi bi-shield-check"></i> Login as Admin
                        </button>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-arrow-left"></i> User Login
                        </a>
                        <a href="../index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-house"></i> Go to Home
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>