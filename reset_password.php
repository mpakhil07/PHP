<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<pre>";

// Set new password to 'admin123'
$new_password = 'admin123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Check if admin exists
$check = $db->query("SELECT * FROM users WHERE email = 'admin@example.com'")->fetch();

if($check) {
    // Update existing admin
    $stmt = $db->prepare("UPDATE users SET password = ? WHERE email = 'admin@example.com'");
    $stmt->execute([$hashed_password]);
    echo "✓ Updated admin password\n";
} else {
    // Create new admin
    $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin@example.com', $hashed_password, 'admin']);
    echo "✓ Created new admin account\n";
}

echo "\nNew credentials:\n";
echo "Email: admin@example.com\n";
echo "Password: $new_password\n";
echo "Hashed: $hashed_password\n\n";

// Verify it worked
$verify = $db->query("SELECT email, password FROM users WHERE email = 'admin@example.com'")->fetch();
if($verify && password_verify($new_password, $verify['password'])) {
    echo "✓ Verification successful! Password matches.\n";
} else {
    echo "✗ Verification failed!\n";
}

echo "\n<a href='auth/admin_login.php'>Go to Admin Login</a>";
echo "</pre>";
?>