<?php
session_start();

// Destroy admin session only
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_role']);

// If no user session exists, destroy entire session
if(!isset($_SESSION['user_id'])) {
    session_destroy();
}

header("Location: ../index.php");
exit();
?>