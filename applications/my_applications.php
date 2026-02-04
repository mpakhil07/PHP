<?php
session_start();
require_once '../includes/auth_check.php';
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get user's applications
$query = "SELECT * FROM applications WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <h2 class="mb-4">
        <i class="bi bi-list-check"></i> My Applications
    </h2>
    
    <?php if(empty($applications)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> You haven't submitted any applications yet.
            <div class="mt-2">
                <a href="apply.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Apply Now
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($applications as $app): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-briefcase"></i> <?php echo htmlspecialchars($app['role']); ?>
                            </h5>
                            <span class="badge 
                                <?php echo $app['status'] == 'pending' ? 'bg-secondary' : 
                                        ($app['status'] == 'accepted' ? 'bg-success' : 
                                        ($app['status'] == 'rejected' ? 'bg-danger' : 'bg-warning')); ?>">
                                <?php echo ucfirst($app['status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong><i class="bi bi-phone"></i> Mobile:</strong> <?php echo htmlspecialchars($app['mobile']); ?></p>
                            <p><strong><i class="bi bi-graph-up"></i> Experience:</strong> 
                                <span class="badge bg-<?php echo $app['experience'] == 'Beginner' ? 'info' : 
                                                        ($app['experience'] == 'Intermediate' ? 'warning' : 'success'); ?>">
                                    <?php echo $app['experience']; ?>
                                </span>
                            </p>
                            <p><strong><i class="bi bi-tools"></i> Skills:</strong></p>
                            <p><?php echo htmlspecialchars($app['skills']); ?></p>
                            
                            <?php if(!empty($app['portfolio_link'])): ?>
                                <p><strong><i class="bi bi-link"></i> Portfolio:</strong>
                                    <a href="<?php echo htmlspecialchars($app['portfolio_link']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-box-arrow-up-right"></i> View
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted">
                            <small>
                                <i class="bi bi-calendar"></i> Applied on: <?php echo date('M d, Y h:i A', strtotime($app['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4">
            <a href="apply.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Submit New Application
            </a>
            <a href="../index.php" class="btn btn-outline-primary">
                <i class="bi bi-house"></i> Back to Home
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>