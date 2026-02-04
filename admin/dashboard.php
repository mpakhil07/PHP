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
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mb-4">
                <i class="bi bi-speedometer2"></i> Admin Dashboard
            </h1>
            
            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-people"></i> Total Users
                            </h5>
                            <?php
                            $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                            $total_users = $stmt->fetch()['count'];
                            ?>
                            <h2><?php echo $total_users; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text"></i> Total Applications
                            </h5>
                            <?php
                            $stmt = $db->query("SELECT COUNT(*) as count FROM applications");
                            $total_applications = $stmt->fetch()['count'];
                            ?>
                            <h2><?php echo $total_applications; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-clock"></i> Pending Applications
                            </h5>
                            <?php
                            $stmt = $db->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'");
                            $pending = $stmt->fetch()['count'];
                            ?>
                            <h2><?php echo $pending; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-shield-check"></i> Admin Users
                            </h5>
                            <?php
                            $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                            $admins = $stmt->fetch()['count'];
                            ?>
                            <h2><?php echo $admins; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> Recent Applications
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $query = "SELECT a.*, u.name, u.email 
                              FROM applications a 
                              INNER JOIN users u ON a.user_id = u.id 
                              ORDER BY a.created_at DESC 
                              LIMIT 5";
                    $stmt = $db->query($query);
                    $recent_apps = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if(empty($recent_apps)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No applications found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Applicant</th>
                                        <th>Role</th>
                                        <th>Experience</th>
                                        <th>Status</th>
                                        <th>Applied Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_apps as $app): ?>
                                        <tr>
                                            <td><?php echo $app['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['name']); ?></strong><br>
                                                <small><?php echo htmlspecialchars($app['email']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary"><?php echo $app['role']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $app['experience'] == 'Beginner' ? 'info' : ($app['experience'] == 'Intermediate' ? 'warning' : 'success'); ?>">
                                                    <?php echo $app['experience']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $app['status'] == 'pending' ? 'secondary' : ($app['status'] == 'accepted' ? 'success' : ($app['status'] == 'rejected' ? 'danger' : 'warning')); ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($app['created_at'])); ?></td>
                                            <td>
                                                <a href="applications.php?view=<?php echo $app['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="applications.php" class="btn btn-primary">
                                <i class="bi bi-eye"></i> View All Applications
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-lightning"></i> Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="applications.php" class="btn btn-primary w-100 mb-2">
                                        <i class="bi bi-file-earmark-text"></i> Manage Applications
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="users.php" class="btn btn-success w-100 mb-2">
                                        <i class="bi bi-people"></i> Manage Users
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="applications.php?status=pending" class="btn btn-warning w-100 mb-2">
                                        <i class="bi bi-clock"></i> Pending Reviews
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="../auth/register.php" class="btn btn-info w-100 mb-2">
                                        <i class="bi bi-person-plus"></i> Add New User
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>