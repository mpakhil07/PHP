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

// Get parameters
$app_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$new_status = isset($_GET['status']) ? $_GET['status'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$experience_filter = isset($_GET['experience']) ? $_GET['experience'] : '';
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Validate status
$allowed_statuses = ['pending', 'reviewed', 'accepted', 'rejected'];
if(!in_array($new_status, $allowed_statuses)) {
    $new_status = 'pending';
}

// Update application status
if($app_id > 0) {
    try {
        $query = "UPDATE applications SET status = :status WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":status", $new_status);
        $stmt->bindParam(":id", $app_id);
        $stmt->execute();
        
        $success = true;
    } catch(PDOException $e) {
        $success = false;
        $error_message = $e->getMessage();
    }
}

// Get application details for confirmation
$app_details = [];
if($app_id > 0) {
    $query = "SELECT a.*, u.name, u.email 
              FROM applications a 
              INNER JOIN users u ON a.user_id = u.id 
              WHERE a.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $app_id);
    $stmt->execute();
    $app_details = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<?php include '../includes/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-arrow-repeat"></i> Update Application Status
                    </h4>
                </div>
                <div class="card-body">
                    <?php if($app_id > 0 && !empty($app_details)): ?>
                        
                        <?php if(isset($success) && $success): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> 
                                <strong>Success!</strong> Application status has been updated.
                            </div>
                            
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5>Application Details:</h5>
                                    <p><strong>Applicant:</strong> <?php echo htmlspecialchars($app_details['name']); ?> (<?php echo htmlspecialchars($app_details['email']); ?>)</p>
                                    <p><strong>Role:</strong> <?php echo htmlspecialchars($app_details['role']); ?></p>
                                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($app_details['experience']); ?></p>
                                    <p><strong>Previous Status:</strong> 
                                        <span class="badge bg-<?php echo $app_details['status'] == 'pending' ? 'secondary' : 
                                                ($app_details['status'] == 'accepted' ? 'success' : 
                                                ($app_details['status'] == 'rejected' ? 'danger' : 'warning')); ?>">
                                            <?php echo ucfirst($app_details['status']); ?>
                                        </span>
                                    </p>
                                    <p><strong>New Status:</strong> 
                                        <span class="badge bg-<?php echo $new_status == 'pending' ? 'secondary' : 
                                                ($new_status == 'accepted' ? 'success' : 
                                                ($new_status == 'rejected' ? 'danger' : 'warning')); ?>">
                                            <?php echo ucfirst($new_status); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                        <?php elseif(isset($success) && !$success): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> 
                                <strong>Error!</strong> Failed to update application status: <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            <strong>Error!</strong> Application not found or invalid ID.
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <a href="applications.php?page=<?php echo $page; ?>&role=<?php echo $role_filter; ?>&experience=<?php echo $experience_filter; ?>&status=<?php echo $status_filter; ?>&search=<?php echo urlencode($search); ?>" 
                           class="btn btn-primary">
                            <i class="bi bi-arrow-left"></i> Back to Applications
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>