<?php
include "../config/db.php";

// Agar session me pending_order nahi hai to wapis checkout bhej do
if (!isset($_SESSION['pending_order'])) {
    header("Location: checkout.php");
    exit;
}

$order = $_SESSION['pending_order'];

// Agar COD nahi hai, transaction_id POST se lena hoga
$transaction_id = isset($_POST['transaction_id']) ? trim($_POST['transaction_id']) : '';

if ($order['payment_method'] !== 'COD' && empty($transaction_id)) {
    die("❌ Transaction ID is required for online payments.");
}

// Insert into orders table
$query = "INSERT INTO orders 
    (product_id, user_id, fullname, email, phone, address, quantity, price, payment_method, note, selected_image, delivery_type, delivery_charges)
    VALUES 
    ('{$order['product_id']}', '{$order['user_id']}', '{$order['fullname']}', '{$order['email']}', '{$order['phone']}', '{$order['address']}', '{$order['quantity']}', '{$order['price']}', '{$order['payment_method']}', '{$order['note']}', '{$order['selected_image']}', '{$order['delivery_type']}', '{$order['delivery_charges']}')";

if (mysqli_query($conn, $query)) {
    $order_id = mysqli_insert_id($conn);

    // COD orders: no transaction record required (optional)
    if ($order['payment_method'] !== 'COD') {
        // Prepare variables for transaction table
        $payment_method = $order['payment_method'];
        $price = $order['price'];

        // Insert into transactions table
        $query2 = "INSERT INTO transactions (order_id, payment_method, amount, reference_no)
                   VALUES ('$order_id', '$payment_method', '$price', '$transaction_id')";
        mysqli_query($conn, $query2);
    }

    // Clear session
    unset($_SESSION['pending_order']);

    // Redirect to thank you page
    header("Location: thank_you.php");
    exit;

} else {
    die("❌ Failed to save order: " . mysqli_error($conn));
}
?>
