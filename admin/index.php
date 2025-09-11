<?php include '../config/db.php'; ?>

<?php
// Agar admin login nahi hai to redirect back to login
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}
?>

<?php include "./includes/navbar.php"; ?>
