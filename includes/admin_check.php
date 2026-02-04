<?php
if(!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/admin_login.php");
    exit();
}
?>