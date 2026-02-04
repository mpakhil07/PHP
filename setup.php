<?php
/**
 * Quick Setup Script for Internship Portal
 * Run this once to verify everything is set up correctly
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Setup Check</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css'>
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .success { color: #198754; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .card { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'><i class='bi bi-gear'></i> Internship Portal Setup Check</h1>";

// Check PHP version
echo "<div class='card'>";
echo "<div class='card-header'><h5 class='mb-0'><i class='bi bi-code-slash'></i> PHP Configuration</h5></div>";
echo "<div class='card-body'>";
if(version_compare(PHP_VERSION, '7.4.0') >= 0) {
    echo "<p class='success'><i class='bi bi-check-circle'></i> PHP Version: " . PHP_VERSION . " (OK)</p>";
} else {
    echo "<p class='error'><i class='bi bi-x-circle'></i> PHP Version: " . PHP_VERSION . " (7.4+ required)</p>";
}

// Check required extensions
$extensions = ['pdo_mysql', 'session', 'mbstring'];
foreach($extensions as $ext) {
    if(extension_loaded($ext)) {
        echo "<p class='success'><i class='bi bi-check-circle'></i> Extension: $ext (Loaded)</p>";
    } else {
        echo "<p class='error'><i class='bi bi-x-circle'></i> Extension: $ext (Not loaded)</p>";
    }
}
echo "</div></div>";

// Database connection test
echo "<div class='card'>";
echo "<div class='card-header'><h5 class='mb-0'><i class='bi bi-database'></i> Database Connection</h5></div>";
echo "<div class='card-body'>";

try {
    require_once 'config/database.php';
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "<p class='success'><i class='bi bi-check-circle'></i> Database connection successful</p>";
    
    // Check if tables exist
    $tables = ['users', 'applications'];
    foreach($tables as $table) {
        $stmt = $conn->query("SHOW TABLES LIKE '$table'");
        if($stmt->rowCount() > 0) {
            echo "<p class='success'><i class='bi bi-check-circle'></i> Table '$table' exists</p>";
            
            // Count records
            $count = $conn->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            echo "<p><i class='bi bi-list'></i> Records in $table: <strong>$count</strong></p>";
        } else {
            echo "<p class='error'><i class='bi bi-x-circle'></i> Table '$table' does not exist</p>";
        }
    }
    
    // Check admin user
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
    $admin_count = $stmt->fetch()['count'];
    if($admin_count > 0) {
        echo "<p class='success'><i class='bi bi-check-circle'></i> Admin user exists</p>";
        
        // Show admin credentials
        $stmt = $conn->query("SELECT name, email FROM users WHERE role = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        echo "<div class='alert alert-warning mt-3'>
                <i class='bi bi-exclamation-triangle'></i> <strong>Admin Login Credentials:</strong><br>
                Email: " . $admin['email'] . "<br>
                Password: Admin@123
              </div>";
    } else {
        echo "<p class='error'><i class='bi bi-x-circle'></i> No admin user found</p>";
    }
    
} catch(PDOException $e) {
    echo "<p class='error'><i class='bi bi-x-circle'></i> Database connection failed: " . $e->getMessage() . "</p>";
    echo "<div class='alert alert-danger mt-3'>
            <h6><i class='bi bi-lightbulb'></i> Common solutions:</h6>
            <ul>
                <li>Check if MySQL is running in MySQL Workbench</li>
                <li>Verify username/password in config/database.php</li>
                <li>Make sure 'internship_portal' database exists</li>
                <li>Check MySQL port (default is 3306)</li>
            </ul>
          </div>";
}
echo "</div></div>";

// File permissions check
echo "<div class='card'>";
echo "<div class='card-header'><h5 class='mb-0'><i class='bi bi-folder'></i> File Structure</h5></div>";
echo "<div class='card-body'>";
$files_to_check = ['index.php', 'config/database.php', 'includes/header.php', 'auth/login.php'];
foreach($files_to_check as $file) {
    if(file_exists($file)) {
        echo "<p class='success'><i class='bi bi-check-circle'></i> File exists: $file</p>";
    } else {
        echo "<p class='error'><i class='bi bi-x-circle'></i> File missing: $file</p>";
    }
}
echo "</div></div>";

// Quick links
echo "<div class='card'>";
echo "<div class='card-header'><h5 class='mb-0'><i class='bi bi-link'></i> Quick Access Links</h5></div>";
echo "<div class='card-body'>";
echo "<div class='row'>";
echo "<div class='col-md-6'>";
echo "<h6>User Section</h6>";
echo "<div class='list-group mb-3'>";
echo "<a href='index.php' class='list-group-item list-group-item-action'><i class='bi bi-house'></i> Home Page</a>";
echo "<a href='auth/register.php' class='list-group-item list-group-item-action'><i class='bi bi-person-plus'></i> User Registration</a>";
echo "<a href='auth/login.php' class='list-group-item list-group-item-action'><i class='bi bi-box-arrow-in-right'></i> User Login</a>";
echo "</div>";
echo "</div>";
echo "<div class='col-md-6'>";
echo "<h6>Admin Section</h6>";
echo "<div class='list-group'>";
echo "<a href='auth/admin_login.php' class='list-group-item list-group-item-action'><i class='bi bi-shield-lock'></i> Admin Login</a>";
echo "<a href='admin/dashboard.php' class='list-group-item list-group-item-action'><i class='bi bi-speedometer2'></i> Admin Dashboard</a>";
echo "<a href='admin/applications.php' class='list-group-item list-group-item-action'><i class='bi bi-file-earmark-text'></i> Manage Applications</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div></div>";

echo "</div>
    <footer class='mt-4 text-center text-muted'>
        <p>Setup check completed. If all checks pass, your application is ready to use!</p>
    </footer>
</body>
</html>";
?>