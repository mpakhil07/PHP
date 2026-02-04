<?php
session_start();

// Destroy user session only
unset($_SESSION['user_id']);
unset($_SESSION['name']);
unset($_SESSION['email']);
unset($_SESSION['role']);

// If no admin session exists, destroy entire session
if(!isset($_SESSION['admin_id'])) {
    session_destroy();
}

header("Location: ../index.php");
exit();
?>