<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only allow access if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to admin login page
    header("Location: ../users/admin_login.php");
    exit;
}
?>