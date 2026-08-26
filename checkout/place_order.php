<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// ✅ Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../users/login.php");
    exit;
}
$errors = [];
$success = null;
$product_name = "Unknown Product";
$order_details = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ Collect & sanitize POST data
    $product_id       = (int)($_POST['product_id'] ?? 0);
    $fullname         = trim($_POST['fullname'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $address          = trim($_POST['address'] ?? '');
    $quantity         = max(1, (int)($_POST['quantity'] ?? 1));
    // Prices and product type are always loaded from the database below; hidden form fields are not trusted.
    $price            = 0.0;
    $payment_method   = trim($_POST['payment_method'] ?? 'COD');
    $note             = trim($_POST['note'] ?? '');
    $delivery_type    = trim($_POST['delivery_type'] ?? 'Standard');
    $delivery_charges = 0.0;
    $selected_image   = trim($_POST['selected_image'] ?? '');
    $product_type     = '';

    $color_id = (int)($_POST['color_id'] ?? 0);
    $size_id = (int)($_POST['size_id'] ?? 0);

    // Fetch product name and image from product_images table
    if ($product_id > 0) {
        $stmt = $conn->prepare("SELECT id, name, price, discount, type, stock_status FROM products WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $product_name = $row['name'];
            $stock_status = $row['stock_status'];
            $product_type = $row['type'];
            $discount = min(100, max(0, (float) $row['discount']));
            $price = max(0, (float) $row['price'] * (1 - $discount / 100));
        }
        $stmt->close();
    }

    if (!isset($row) || !$row) $errors[] = 'This product is no longer available.';
    if (($stock_status ?? 'out') !== 'in') $errors[] = 'This product is currently out of stock.';

    $allowedDelivery = ['Standard' => 250.0, 'Fast' => 500.0];
    if (!array_key_exists($delivery_type, $allowedDelivery)) {
        $errors[] = 'Please select a valid delivery option.';
    } else {
        $delivery_charges = $allowedDelivery[$delivery_type];
    }

    // Verify that the selected product image actually belongs to this product.
    $imageStmt = $conn->prepare('SELECT image FROM product_images WHERE product_id = ? AND image = ? LIMIT 1');
    $imageStmt->bind_param('is', $product_id, $selected_image);
    $imageStmt->execute();
    $validImage = $imageStmt->get_result()->fetch_assoc();
    $imageStmt->close();
    if (!$validImage) {
        $fallbackStmt = $conn->prepare('SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1');
        $fallbackStmt->bind_param('i', $product_id);
        $fallbackStmt->execute();
        $fallback = $fallbackStmt->get_result()->fetch_assoc();
        $fallbackStmt->close();
        $selected_image = $fallback['image'] ?? 'placeholder.png';
    }

    // Calculate totals
    $subtotal = $price * $quantity;
    $total_amount = $subtotal + $delivery_charges;

    // ✅ Validation
    if ($fullname === '') $errors[] = "Full Name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "A valid email is required.";
    if ($phone === '') $errors[] = "Phone Number is required.";
    if ($address === '') $errors[] = "Address is required.";
    if ($payment_method === '') $errors[] = "Payment method is required.";
    if ($delivery_type === '') $errors[] = "Delivery type is required.";
    if ($quantity > 20) $errors[] = "A maximum of 20 items can be ordered at once.";
    if (!in_array($payment_method, ['COD', 'EasyPaisa', 'Bank Transfer'], true)) $errors[] = "Please select a valid payment method.";

    // Additional validation for exclusive products
    if ($product_type === 'exclusive') {
        if ($color_id <= 0) $errors[] = "Color selection is required for exclusive products.";
        if ($size_id <= 0) $errors[] = "Size selection is required for exclusive products.";
        $variantStmt = $conn->prepare('SELECT (SELECT COUNT(*) FROM product_colors WHERE product_id = ? AND color_id = ?) AS valid_color, (SELECT COUNT(*) FROM product_sizes WHERE product_id = ? AND size_id = ?) AS valid_size');
        $variantStmt->bind_param('iiii', $product_id, $color_id, $product_id, $size_id);
        $variantStmt->execute();
        $variant = $variantStmt->get_result()->fetch_assoc();
        $variantStmt->close();
        if (!$variant || !(int) $variant['valid_color'] || !(int) $variant['valid_size']) $errors[] = 'Please select valid product options.';
    } else {
        $color_id = 0;
        $size_id = 0;
    }

    // ✅ Process order if no errors
    if (empty($errors)) {
        // Start transaction
        $conn->begin_transaction();

        try {
            if ($payment_method === "COD") {
                // Insert order with color and size for exclusive products
                $stmt = $conn->prepare("INSERT INTO orders 
                    (product_id, user_id, fullname, email, phone, address, quantity, price, 
                     payment_method, note, selected_image, delivery_type, delivery_charges, color_id, size_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                // Bind params → types: i (int), s (string), d (double/float)
                $stmt->bind_param(
                    "iissssidssssdii",
                    $product_id,
                    $user_id,
                    $fullname,
                    $email,
                    $phone,
                    $address,
                    $quantity,
                    $price,
                    $payment_method,
                    $note,
                    $selected_image,
                    $delivery_type,
                    $delivery_charges,
                    $color_id,
                    $size_id
                );

                if ($stmt->execute()) {
                    $order_id = $stmt->insert_id;
                    $stmt->close();

                    $conn->commit();

                    // ✅ Redirect to thank you page for COD orders
                    $_SESSION['order_success'] = true;
                    header("Location: thank_you.php");
                    exit;
                } else {
                    $errors[] = "Failed to place order: " . $stmt->error;
                    $stmt->close();
                    $conn->rollback();
                }
            } else {
                // ✅ For online payments, redirect with session
                $_SESSION['pending_order'] = [
                    'product_id'       => $product_id,
                    'user_id'          => $user_id,
                    'fullname'         => $fullname,
                    'email'            => $email,
                    'phone'            => $phone,
                    'address'          => $address,
                    'quantity'         => $quantity,
                    'price'            => $price,
                    'payment_method'   => $payment_method,
                    'note'             => $note,
                    'selected_image'   => $selected_image,
                    'delivery_type'    => $delivery_type,
                    'delivery_charges' => $delivery_charges,
                    'product_name'     => $product_name,
                    'subtotal'         => $subtotal,
                    'total_amount'     => $total_amount,
                    'color_id'         => $color_id,
                    'size_id'          => $size_id,
                    'product_type'     => $product_type
                ];
                $conn->commit();
                header("Location: transaction_form.php");
                exit;
            }
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Transaction failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaymart - Order Processing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .processing-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 800px;
            margin: 40px auto;
            text-align: center;
        }

        .processing-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px 20px;
            text-align: center;
        }

        .processing-header h1 {
            font-weight: 700;
            margin-bottom: 5px;
            font-size: 2rem;
        }

        .processing-header p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .processing-body {
            padding: 30px;
        }

        .processing-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .processing-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .processing-message {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .alert {
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
        }

        .alert-danger {
            background-color: #fff5f5;
            color: #e53e3e;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
            text-align: left;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 25px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-outline-secondary {
            background: white;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .btn-outline-secondary:hover {
            background: #f8f9fa;
            color: #495057;
        }

        .error-icon {
            color: #e53e3e;
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .buttons-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        @media (max-width: 768px) {
            .processing-container {
                margin: 20px auto;
            }

            .processing-body {
                padding: 20px;
            }

            .buttons-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="processing-container">
        <div class="processing-header">
            <h1><i class="fas fa-shopping-bag me-2"></i> Slaymart</h1>
            <p>Your Fashion Destination</p>
        </div>
        <div class="processing-body">
            <?php if (!empty($errors)): ?>
                <i class="fas fa-exclamation-circle error-icon"></i>
                <h2 class="processing-title">Order Failed</h2>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="buttons-container">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i> Go Back
                    </a>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home me-2"></i> Home
                    </a>
                </div>
            <?php else: ?>
                <i class="fas fa-spinner fa-spin processing-icon"></i>
                <h2 class="processing-title">Processing Order</h2>
                <p class="processing-message">Please wait while we confirm your order...</p>
                <div class="spinner-border text-primary mt-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
