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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --amazon-blue: #131921;
            --amazon-orange: #FF9900;
            --amazon-light-orange: #FFD814;
            --amazon-dark-blue: #232F3E;
            --amazon-light-gray: #F7F7F7;
            --amazon-border: #D5D9D9;
            --amazon-text: #0F1111;
            --amazon-light-text: #565959;
            --amazon-star: #FFA41C;
            --border-radius: 4px;
            --transition: all 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #fff;
            color: var(--amazon-text);
            line-height: 1.5;
            font-size: 14px;
        }
        
        .order-confirmation-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .page-header {
            background: var(--amazon-blue);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 400;
            margin: 0;
        }
        
        /* Main Content */
        .confirmation-card {
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--amazon-border);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .success-header {
            background: var(--amazon-light-gray);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid var(--amazon-border);
        }
        
        .success-icon {
            font-size: 60px;
            color: #007600;
            margin-bottom: 15px;
        }
        
        .success-header h1 {
            font-size: 28px;
            font-weight: 400;
            margin-bottom: 10px;
        }
        
        .success-header p {
            font-size: 16px;
            color: var(--amazon-light-text);
            margin: 0;
        }
        
        /* Order Details */
        .order-details {
            padding: 30px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 20px;
            color: var(--amazon-text);
        }
        
        .product-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
        }
        
        .product-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 1px solid var(--amazon-border);
            border-radius: var(--border-radius);
            padding: 10px;
            background: white;
        }
        
        .product-details h3 {
            font-size: 18px;
            font-weight: 400;
            margin-bottom: 10px;
            color: var(--amazon-text);
        }
        
        .product-details p {
            margin-bottom: 8px;
            color: var(--amazon-light-text);
            font-size: 14px;
        }
        
        .product-details p i {
            margin-right: 8px;
            color: var(--amazon-orange);
        }
        
        /* Order Info Grid */
        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .info-card h4 {
            font-size: 14px;
            font-weight: 500;
            color: var(--amazon-light-text);
            margin-bottom: 5px;
        }
        
        .info-card p {
            font-size: 16px;
            font-weight: 400;
            color: var(--amazon-text);
            margin: 0;
        }
        
        .total-price {
            grid-column: span 2;
            background: white;
            border: 1px solid var(--amazon-border);
        }
        
        .total-price h4 {
            color: var(--amazon-light-text);
        }
        
        .total-price p {
            font-size: 20px;
            color: var(--amazon-text);
        }
        
        .note-section {
            grid-column: span 2;
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
            padding: 15px;
        }
        
        .note-section h4 {
            font-size: 14px;
            font-weight: 500;
            color: var(--amazon-light-text);
            margin-bottom: 5px;
        }
        
        .note-section p {
            font-size: 14px;
            color: var(--amazon-text);
            margin: 0;
        }
        
        /* Next Steps */
        .next-steps {
            margin-bottom: 30px;
        }
        
        .steps-list {
            list-style: none;
        }
        
        .steps-list li {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
            padding: 15px;
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
        }
        
        .step-icon {
            background: var(--amazon-orange);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 14px;
        }
        
        .step-text {
            font-size: 14px;
            color: var(--amazon-text);
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 12px 20px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: var(--transition);
            font-size: 14px;
        }
        
        .btn-success {
            background: var(--amazon-orange);
            color: var(--amazon-blue);
            border: none;
        }
        
        .btn-success:hover {
            background: var(--amazon-light-orange);
            color: var(--amazon-blue);
        }
        
        .btn-outline-secondary {
            background: white;
            color: var(--amazon-text);
            border: 1px solid var(--amazon-border);
        }
        
        .btn-outline-secondary:hover {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .order-info-grid {
                grid-template-columns: 1fr;
            }
            
            .total-price, .note-section {
                grid-column: span 1;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 576px) {
            .order-confirmation-container {
                padding: 0 10px;
            }
            
            .success-header {
                padding: 20px;
            }
            
            .success-icon {
                font-size: 40px;
            }
            
            .success-header h1 {
                font-size: 20px;
            }
            
            .order-details {
                padding: 20px;
            }
            
            .steps-list li {
                gap: 10px;
            }
            
            .step-icon {
                width: 25px;
                height: 25px;
                font-size: 12px;
            }
            
            .step-text {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="order-confirmation-container">
            <h1 class="page-title">Order Confirmation</h1>
        </div>
    </div>
    
    <div class="order-confirmation-container">
        <div class="confirmation-card">
            <div class="success-header">
                <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                <h1>Thank you for your order!</h1>
                <p>Your order has been placed successfully.</p>
            </div>
            
            <div class="order-details">
                <h2 class="section-title">Order Details</h2>
                
                <div class="product-info">
                    <img src="../images/uploads/<?= htmlspecialchars($product_image) ?>" alt="Product Image" class="product-image">
                    <div class="product-details">
                        <h3><?= htmlspecialchars($product_name) ?></h3>
                        <p><i class="fas fa-calendar-alt"></i> <?= date('F j, Y', strtotime($created_at)) ?></p>
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
                
                <div class="next-steps">
                    <h3 class="section-title">What's Next?</h3>
                    <ul class="steps-list">
                        <li>
                            <div class="step-icon">1</div>
                            <div class="step-text">You will receive a confirmation email with your order details</div>
                        </li>
                        <li>
                            <div class="step-icon">2</div>
                            <div class="step-text">Our team will process your order and prepare it for shipment</div>
                        </li>
                        <li>
                            <div class="step-icon">3</div>
                            <div class="step-text">Tracking information will be sent once your order is shipped</div>
                        </li>
                        <li>
                            <div class="step-icon">4</div>
                            <div class="step-text">Your order will be delivered within the estimated timeframe</div>
                        </li>
                    </ul>
                </div>
                
                <div class="action-buttons">
                    <a href="../index.php" class="btn btn-success">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <a href="../users/my_orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-list-alt"></i> View My Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>