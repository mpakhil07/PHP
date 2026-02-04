<?php
session_start();
?>
<?php include 'includes/header.php'; ?>

<div class="text-center py-5">
    <h1 class="display-4">Welcome to Internship Portal</h1>
    
    <?php if(isset($_SESSION['admin_id'])): ?>
        <p class="lead text-success">Welcome back, Administrator! Manage the portal from your dashboard.</p>
        <div class="mt-4">
            <a href="admin/dashboard.php" class="btn btn-success btn-lg me-2">
                <i class="bi bi-speedometer2"></i> Go to Admin Dashboard
            </a>
            <a href="admin/applications.php" class="btn btn-primary btn-lg">
                <i class="bi bi-file-earmark-text"></i> Manage Applications
            </a>
        </div>
    <?php elseif(isset($_SESSION['user_id'])): ?>
        <p class="lead">Apply for exciting internship opportunities and kickstart your career</p>
        <div class="mt-4">
            <a href="applications/apply.php" class="btn btn-success btn-lg me-2">
                <i class="bi bi-plus-circle"></i> Apply for Internship
            </a>
            <a href="applications/my_applications.php" class="btn btn-outline-success btn-lg">
                <i class="bi bi-list-check"></i> View My Applications
            </a>
        </div>
    <?php else: ?>
        <p class="lead">Apply for exciting internship opportunities and kickstart your career</p>
        <div class="mt-4">
            <a href="auth/register.php" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-person-plus"></i> Get Started as User
            </a>
            <a href="auth/login.php" class="btn btn-outline-primary btn-lg me-2">
                <i class="bi bi-box-arrow-in-right"></i> User Login
            </a>
            <a href="auth/admin_login.php" class="btn btn-warning btn-lg">
                <i class="bi bi-shield-lock"></i> Admin Login
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-code-slash text-primary"></i> PHP Developer
                </h5>
                <p class="card-text">Work on backend development using PHP, MySQL, and modern frameworks.</p>
                <?php
                if(isset($_SESSION['admin_id'])) {
                    require_once 'config/database.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    $stmt = $db->query("SELECT COUNT(*) as count FROM applications WHERE role = 'PHP Developer'");
                    $count = $stmt->fetch()['count'];
                    echo "<span class='badge bg-primary'>Total Applications: $count</span>";
                } else {
                    echo "<span class='badge bg-primary'>Open Positions: 5</span>";
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-camera-video text-success"></i> Video Editor
                </h5>
                <p class="card-text">Create engaging video content using Adobe Premiere Pro and After Effects.</p>
                <?php
                if(isset($_SESSION['admin_id'])) {
                    require_once 'config/database.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    $stmt = $db->query("SELECT COUNT(*) as count FROM applications WHERE role = 'Video Editor'");
                    $count = $stmt->fetch()['count'];
                    echo "<span class='badge bg-success'>Total Applications: $count</span>";
                } else {
                    echo "<span class='badge bg-success'>Open Positions: 3</span>";
                }
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-phone text-info"></i> Mobile App Developer
                </h5>
                <p class="card-text">Develop cross-platform mobile applications using React Native.</p>
                <?php
                if(isset($_SESSION['admin_id'])) {
                    require_once 'config/database.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    $stmt = $db->query("SELECT COUNT(*) as count FROM applications WHERE role = 'Mobile App Developer'");
                    $count = $stmt->fetch()['count'];
                    echo "<span class='badge bg-info'>Total Applications: $count</span>";
                } else {
                    echo "<span class='badge bg-info'>Open Positions: 4</span>";
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>