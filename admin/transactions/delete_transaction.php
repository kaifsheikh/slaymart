<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Admin session check

// Check admin login
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}

if (isset($_GET['id'])) {
    $transaction_id = intval($_GET['id']);

    // Delete transaction
    $query = "DELETE FROM transactions WHERE id=$transaction_id";
    mysqli_query($conn, $query);

    // Redirect back to admin transactions
    header("Location: admin_transactions.php");
    exit;
} else {
    die("Transaction ID missing");
}
