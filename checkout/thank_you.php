<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Get last order
$orderQuery = $conn->prepare("
    SELECT o.id, o.payment_method, o.delivery_type, o.delivery_charges, 
           o.price, o.quantity, o.note, o.created_at, 
           o.selected_image,  -- ✅ directly bring selected_image
           p.name AS product_name
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.id DESC 
    LIMIT 1
");
$orderQuery->bind_param("i", $user_id);
$orderQuery->execute();
$orderResult = $orderQuery->get_result()->fetch_assoc();
$orderQuery->close();

if (!$orderResult) {
    die("No order found.");
}

// ✅ Assign values
$payment_method   = $orderResult['payment_method'] ?? 'Cash on Delivery';
$delivery_type    = $orderResult['delivery_type'] ?? 'Standard';
$delivery_charges = (float)($orderResult['delivery_charges'] ?? 0);
$product_name     = $orderResult['product_name'] ?? 'Unknown Product';
$price            = (float)$orderResult['price']; 
$quantity         = (int)$orderResult['quantity'];
$note             = $orderResult['note'] ?? '';
$created_at       = $orderResult['created_at'];

// ✅ Use selected_image from orders table
$product_image = $orderResult['selected_image'] ?? 'placeholder.png';
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>Slaymart - Order Confirmed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html {
            height: 100%;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
            position: relative;
            overflow-x: hidden;
        }
        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            top: 0;
            left: 0;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }
        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 70%;
            animation-delay: 6s;
        }
        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            33% {
                transform: translateY(-30px) rotate(120deg);
            }
            66% {
                transform: translateY(30px) rotate(240deg);
            }
        }
        .thank-you-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 40px);
            padding: 20px 0;
        }
        .thank-you-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 600px;
            z-index: 1;
            animation: slideUp 0.8s ease-out;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .thank-you-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .thank-you-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            animation: pulse 4s infinite ease-in-out;
        }
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 0.5;
            }
            50% {
                transform: scale(1.1);
                opacity: 0.3;
            }
        }
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounceIn 1s ease;
            position: relative;
            z-index: 1;
        }
        @keyframes bounceIn {
            0%   { transform: scale(0.3); opacity: 0; }
            50%  { transform: scale(1.1); opacity: 1; }
            70%  { transform: scale(0.9); }
            100% { transform: scale(1); }
        }
        .thank-you-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        .thank-you-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        .thank-you-body {
            padding: 40px 30px;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .order-details h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
            position: relative;
            padding-bottom: 15px;
        }
        .order-details h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        .product-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        .product-details h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        .product-details p {
            margin-bottom: 5px;
            color: #6c757d;
            font-size: 0.95rem;
        }
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }
        .info-card:hover {
            transform: translateY(-5px);
        }
        .info-card h4 {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-card p {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        .total-price {
            grid-column: span 2;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-align: center;
        }
        .total-price h4 {
            color: rgba(255, 255, 255, 0.9);
        }
        .total-price p {
            font-size: 1.3rem;
            color: white;
        }
        .note-section {
            grid-column: span 2;
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }
        .note-section h4 {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .note-section p {
            font-size: 1rem;
            color: #2c3e50;
            margin: 0;
            font-style: italic;
        }
        .next-steps {
            margin-bottom: 30px;
        }
        .next-steps h3 {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .steps-list {
            list-style: none;
        }
        .steps-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            transition: transform 0.3s ease;
        }
        .steps-list li:hover {
            transform: translateX(5px);
        }
        .step-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        .step-text {
            font-size: 1rem;
            color: #495057;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 15px 20px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .btn-success {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
        }
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
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
        /* Responsive Design */
        @media (max-width: 768px) {
            .thank-you-header {
                padding: 30px 20px;
            }
            
            .success-icon {
                font-size: 60px;
            }
            
            .thank-you-header h1 {
                font-size: 2rem;
            }
            
            .thank-you-body {
                padding: 30px 20px;
            }
            
            .order-details, .next-steps {
                padding: 20px;
                margin-bottom: 20px;
            }
            
            .order-details h2, .next-steps h3 {
                font-size: 1.1rem;
            }
            
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .total-price, .note-section {
                grid-column: span 1;
            }
        }
        @media (max-width: 576px) {
            body {
                padding: 10px;
            }
            
            .thank-you-wrapper {
                min-height: auto;
                padding: 0;
            }
            
            .thank-you-container {
                border-radius: 15px;
            }
            
            .thank-you-header {
                padding: 25px 15px;
            }
            
            .success-icon {
                font-size: 50px;
            }
            
            .thank-you-header h1 {
                font-size: 1.8rem;
            }
            
            .thank-you-header p {
                font-size: 1rem;
            }
            
            .thank-you-body {
                padding: 25px 15px;
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .steps-list li {
                gap: 10px;
            }
            
            .step-icon {
                width: 25px;
                height: 25px;
                font-size: 0.8rem;
            }
            
            .step-text {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="thank-you-wrapper">
        <div class="thank-you-container">
            <div class="thank-you-header">
                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                <h1>Order Confirmed!</h1>
                <p>Thank you for shopping with Slaymart</p>
            </div>
            
            <div class="thank-you-body">
                <div class="order-details">
                    <h2 class="mb-3">Order Confirmation</h2>
                    
                    <div class="product-info">
                        <img src="../images/uploads/<?= htmlspecialchars($product_image) ?>" alt="Product Image" class="product-image">
                        <div>
                            <h4><?= htmlspecialchars($product_name) ?></h4>
                            <p><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($created_at) ?></p>
                            <p><i class="fas fa-boxes"></i> Quantity: <?= $quantity ?></p>
                        </div>
                    </div>
                    
                    <div class="order-info-grid">
                        <div class="info-card">
                            <h4>Unit Price</h4>
                            <p>PKR <?= number_format(($price - $delivery_charges) / $quantity) ?></p>
                        </div>
                        <div class="info-card">
                            <h4>Delivery Type</h4>
                            <p><?= htmlspecialchars($delivery_type) ?></p>
                        </div>
                        <div class="info-card">
                            <h4>Delivery Charges</h4>
                            <p>PKR <?= number_format($delivery_charges) ?></p>
                        </div>
                        <div class="info-card">
                            <h4>Payment Method</h4>
                            <p><?= htmlspecialchars($payment_method) ?></p>
                        </div>
                        <div class="total-price">
                            <h4>Total Amount</h4>
                            <p>PKR <?= number_format($price) ?></p>
                        </div>
                        <?php if (!empty($note)): ?>
                        <div class="note-section">
                            <h4>Special Note</h4>
                            <p><?= nl2br(htmlspecialchars($note)) ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h3 class="mt-4 mb-3"><i class="fas fa-tasks"></i> What's Next?</h3>
                    <ul class="steps-list">
                        <li><div class="step-icon">1</div><div>You will receive a confirmation email with your order details</div></li>
                        <li><div class="step-icon">2</div><div>Our team will process your order and prepare it for shipment</div></li>
                        <li><div class="step-icon">3</div><div>Tracking information will be sent once your order is shipped</div></li>
                        <li><div class="step-icon">4</div><div>Your order will be delivered within the estimated timeframe</div></li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <a href="../index.php" class="btn btn-success"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
                    <a href="../users/my_orders.php" class="btn btn-outline-secondary"><i class="fas fa-list-alt"></i> View My Orders</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>