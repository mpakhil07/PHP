<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<!DOCTYPE html>
<html>
<head>
    <title>Check Admin Password</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css'>
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .match { color: green; font-weight: bold; }
        .nomatch { color: red; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'><i class='bi bi-key'></i> Admin Password Checker</h1>";

try {
    // Check if admin account exists
    $query = "SELECT id, name, email, password, role, created_at FROM users WHERE email = 'admin@example.com'";
    $stmt = $db->query($query);
    
    if($stmt->rowCount() == 0) {
        echo "<div class='alert alert-danger'>
                <i class='bi bi-exclamation-triangle'></i> No admin account found with email: admin@example.com
              </div>";
        
        // Show all admin users
        $all_admins = $db->query("SELECT email, role FROM users WHERE role = 'admin'")->fetchAll();
        if(count($all_admins) > 0) {
            echo "<div class='alert alert-info'>
                    <h5>Existing Admin Accounts:</h5>";
            foreach($all_admins as $admin) {
                echo "<p><strong>" . htmlspecialchars($admin['email']) . "</strong> - Role: " . $admin['role'] . "</p>";
            }
            echo "</div>";
        }
    } else {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div class='card mb-4'>
                <div class='card-header bg-primary text-white'>
                    <h5 class='mb-0'><i class='bi bi-person-badge'></i> Admin Account Found</h5>
                </div>
                <div class='card-body'>
                    <pre>";
        echo "ID: " . $admin['id'] . "\n";
        echo "Name: " . htmlspecialchars($admin['name']) . "\n";
        echo "Email: " . htmlspecialchars($admin['email']) . "\n";
        echo "Role: " . $admin['role'] . "\n";
        echo "Created: " . $admin['created_at'] . "\n";
        echo "Password Hash: " . $admin['password'] . "\n";
        echo "Hash Length: " . strlen($admin['password']) . " characters\n";
        echo "</pre>
                </div>
            </div>";
        
        // Test common passwords
        echo "<div class='card mb-4'>
                <div class='card-header'>
                    <h5 class='mb-0'><i class='bi bi-shield-lock'></i> Test Common Passwords</h5>
                </div>
                <div class='card-body'>";
        
        $test_passwords = [
            'Admin@123',
            'admin123', 
            'password123',
            'admin',
            'password',
            'Admin123',
            'admin@123',
            'Admin@1234',
            '123456',
            'adminadmin',
            'Password123',
            'P@ssw0rd'
        ];
        
        echo "<table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Password to Test</th>
                        <th>Result</th>
                        <th>Login Link</th>
                    </tr>
                </thead>
                <tbody>";
        
        $found_match = false;
        foreach($test_passwords as $pwd) {
            $match = password_verify($pwd, $admin['password']);
            echo "<tr>
                    <td><code>$pwd</code></td>
                    <td class='" . ($match ? 'match' : 'nomatch') . "'>";
            echo $match ? '<i class="bi bi-check-circle"></i> MATCHES!' : '<i class="bi bi-x-circle"></i> No match';
            echo "</td>
                    <td>";
            if($match) {
                $found_match = true;
                echo "<button class='btn btn-sm btn-success' onclick=\"testLogin('" . htmlspecialchars($admin['email']) . "', '$pwd')\">
                        <i class='bi bi-box-arrow-in-right'></i> Test Login
                      </button>";
            }
            echo "</td>
                  </tr>";
        }
        
        echo "</tbody></table>";
        
        if(!$found_match) {
            echo "<div class='alert alert-warning'>
                    <i class='bi bi-exclamation-triangle'></i> None of the common passwords matched!
                    The stored hash doesn't match any of our test passwords.
                  </div>";
        }
        
        echo "</div></div>";
        
        // Show hash format information
        echo "<div class='card'>
                <div class='card-header'>
                    <h5 class='mb-0'><i class='bi bi-info-circle'></i> Hash Information</h5>
                </div>
                <div class='card-body'>
                    <p><strong>Hash Format:</strong> " . substr($admin['password'], 0, 60) . "</p>
                    <p><strong>Hash Algorithm:</strong> " . (strpos($admin['password'], '$2y$') === 0 ? 'bcrypt' : 'Unknown') . "</p>
                    <p><strong>Cost Factor:</strong> " . (strpos($admin['password'], '$2y$10$') === 0 ? '10 (normal)' : 'Unknown') . "</p>
                </div>
            </div>";
    }
    
    // Show all users for reference
    echo "<div class='card mt-4'>
            <div class='card-header'>
                <h5 class='mb-0'><i class='bi bi-people'></i> All Users in Database</h5>
            </div>
            <div class='card-body'>
                <div class='table-responsive'>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>";
    
    $all_users = $db->query("SELECT * FROM users ORDER BY role DESC, id ASC")->fetchAll();
    foreach($all_users as $user) {
        $role_class = $user['role'] == 'admin' ? 'badge bg-danger' : 'badge bg-primary';
        echo "<tr>
                <td>{$user['id']}</td>
                <td>" . htmlspecialchars($user['name']) . "</td>
                <td>" . htmlspecialchars($user['email']) . "</td>
                <td><span class='$role_class'>{$user['role']}</span></td>
                <td>" . date('M d, Y', strtotime($user['created_at'])) . "</td>
              </tr>";
    }
    
    echo "</tbody></table></div></div></div>";

} catch(Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}

echo "<div class='mt-4'>
        <a href='auth/admin_login.php' class='btn btn-primary'>
            <i class='bi bi-shield-lock'></i> Go to Admin Login
        </a>
        <a href='index.php' class='btn btn-secondary'>
            <i class='bi bi-house'></i> Go to Home
        </a>
        <button class='btn btn-warning' onclick='resetAdminPassword()'>
            <i class='bi bi-arrow-clockwise'></i> Reset Admin Password
        </button>
      </div>
    </div>
    
    <script>
    function testLogin(email, password) {
        alert('To test login:\\nEmail: ' + email + '\\nPassword: ' + password + '\\n\\nGo to admin login page and try these credentials.');
        window.open('auth/admin_login.php', '_blank');
    }
    
    function resetAdminPassword() {
        if(confirm('This will reset admin@example.com password to \"admin123\". Continue?')) {
            window.location.href = 'reset_password.php';
        }
    }
    </script>
</body>
</html>";
?>