<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Session Check

// Check parameters
if (!isset($_GET['id']) || !isset($_GET['status'])) {
    die("Missing parameters.");
}

$id = (int) $_GET['id'];
$status = $_GET['status'];

// Allowed statuses
$allowed_statuses = ['pending', 'shipped', 'processing', 'delivered'];

// Validate
if (!in_array($status, $allowed_statuses)) {
    die("Invalid status.");
}

// Update status
$query = "UPDATE orders SET status = '$status' WHERE id = $id";

if (mysqli_query($conn, $query)) {
    header("Location: customer_orders.php");
    exit;
} else {
    die("Failed to update order: " . mysqli_error($conn));
}
?>
