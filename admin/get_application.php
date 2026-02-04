<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($app_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

try {
    $query = "SELECT a.*, u.name, u.email 
              FROM applications a 
              INNER JOIN users u ON a.user_id = u.id 
              WHERE a.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $app_id);
    $stmt->execute();
    
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($application) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'application' => $application
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Application not found'
        ]);
    }
    
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>