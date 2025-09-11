<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Admin session check

// Check admin login
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}

// Get transaction id and status
if (isset($_GET['id']) && isset($_GET['status'])) {
    $transaction_id = intval($_GET['id']);
    $status = $_GET['status']; // 'success' or 'failed'

    // Validate status
    if (!in_array($status, ['success', 'failed'])) {
        die("Invalid status");
    }
    
    // Update transactions table only
    $query = "UPDATE transactions SET status='$status' WHERE id=$transaction_id";
    mysqli_query($conn, $query);

    // No change to orders.delivery_status — now payment & delivery are independent

    // Redirect back to admin transactions
    header("Location: admin_transactions.php");
    exit;

} else {
    die("Transaction ID or status missing");
}
