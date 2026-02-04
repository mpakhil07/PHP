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

// Get POST data
$app_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate status
$allowed_statuses = ['pending', 'reviewed', 'accepted', 'rejected'];
if(!in_array($new_status, $allowed_statuses) || $app_id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

try {
    // Update application status
    $query = "UPDATE applications SET status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":status", $new_status);
    $stmt->bindParam(":id", $app_id);
    
    if($stmt->execute()) {
        // Get updated application for response
        $query = "SELECT a.*, u.name FROM applications a 
                  INNER JOIN users u ON a.user_id = u.id 
                  WHERE a.id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":id", $app_id);
        $stmt->execute();
        $application = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'application' => $application
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update status'
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