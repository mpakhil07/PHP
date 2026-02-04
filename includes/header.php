<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('BASE_URL','/internship-portal/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-briefcase"></i> Internship Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Regular User Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>applications/apply.php">
                                <i class="bi bi-plus-circle"></i> Apply Now
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>applications/my_applications.php">
                                <i class="bi bi-list-check"></i> My Applications
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>auth/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php elseif(isset($_SESSION['admin_id'])): ?>
                        <!-- Admin Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/applications.php">
                                <i class="bi bi-file-earmark-text"></i> Applications
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>admin/users.php">
                                <i class="bi bi-people"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <span class="nav-link text-warning">
                                <i class="bi bi-shield-check"></i> Admin: <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>auth/admin_logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <!-- Guest Menu -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>auth/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> User Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= BASE_URL ?>auth/register.php">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-warning btn-sm" href="<?= BASE_URL ?>auth/admin_login.php">
                                <i class="bi bi-shield-lock"></i> Admin Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">