<?php
include '../../config/db.php';
include '../includes/session_check.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = $_GET['id'];

    // Prevent admin from deleting themselves or critical roles (optional)
    // You can add role-based protection here if needed

    $query = "DELETE FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        header("Location: users_manage.php");
        exit;
    } else {
        echo "❌ Error deleting user.";
    }
} else {
    echo "❌ Invalid user ID.";
}
?>
