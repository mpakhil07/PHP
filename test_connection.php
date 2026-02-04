<?php
echo "<h3>MySQL Connection Test</h3>";

// Test different connection methods
$configs = [
    ['host' => 'localhost', 'port' => 3306, 'username' => 'root', 'password' => ''],
    ['host' => '127.0.0.1', 'port' => 3306, 'username' => 'root', 'password' => ''],
    ['host' => 'localhost:3306', 'port' => 3306, 'username' => 'root', 'password' => '']
];

foreach($configs as $config) {
    echo "<h4>Trying: {$config['host']}:{$config['port']}</h4>";
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
            $config['username'],
            $config['password']
        );
        echo "<span style='color:green'>✓ Connection successful</span><br>";
        
        // Check databases
        $stmt = $pdo->query("SHOW DATABASES LIKE 'internship_portal'");
        if($stmt->rowCount() > 0) {
            echo "<span style='color:green'>✓ Database 'internship_portal' exists</span><br>";
        } else {
            echo "<span style='color:red'>✗ Database 'internship_portal' not found</span><br>";
        }
        
    } catch(PDOException $e) {
        echo "<span style='color:red'>✗ Connection failed: " . $e->getMessage() . "</span><br>";
    }
    echo "<hr>";
}

// Check PHP extensions
echo "<h4>PHP Extensions Check:</h4>";
$required_extensions = ['pdo_mysql', 'session', 'mbstring'];
foreach($required_extensions as $ext) {
    if(extension_loaded($ext)) {
        echo "<span style='color:green'>✓ $ext loaded</span><br>";
    } else {
        echo "<span style='color:red'>✗ $ext NOT loaded</span><br>";
    }
}
?>