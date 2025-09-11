<?php
include "../../config/db.php";
include "../includes/session_check.php";

if (!isset($_GET['id'])) {
    echo "Order ID missing.";
    exit;
}

$order_id = $_GET['id'];

// Fetch order details
$query = "
   SELECT 
    o.*, 
    p.name AS product_name,
    p.category AS product_category,
    o.selected_image AS product_image,
    p.price AS original_price,
    p.discount,
    o.delivery_type
FROM orders o
JOIN products p ON o.product_id = p.id
WHERE o.id = $order_id
";

$result = mysqli_query($conn, $query);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    echo "No order found!";
    exit;
}

// Check if the order already has the final calculated price
// If so, use that instead of recalculating
if (isset($order['price']) && $order['price'] > 0) {
    // The price in the orders table is the final price (after discount and delivery)
    $final_price = $order['price'];
    
    // If we have the original price and discount, we can calculate the breakdown
    $original_price = $order['original_price'];
    $discount_percentage = $order['discount'];
    $quantity = $order['quantity'];
    
    // Calculate discount amount
    $discount_amount = $original_price * ($discount_percentage / 100);
    
    // Calculate price after discount
    $unit_price_after_discount = $original_price - $discount_amount;
    
    // Calculate total product price after discount
    $product_total_after_discount = $unit_price_after_discount * $quantity;
    
    // Calculate total product price before discount (for display)
    $product_total_before_discount = $original_price * $quantity;
    
    // Calculate total discount amount
    $total_discount_amount = $discount_amount * $quantity;
    
    // Delivery charges logic
    $delivery_type = $order['delivery_type'] ?? "Standard";
    $delivery_charges = 0;
    if ($delivery_type == "Standard") {
        $delivery_charges = 250;
    } elseif ($delivery_type == "Fast") {
        $delivery_charges = 500;
    }
    
    // Verify our calculations match the stored price
    $calculated_total = $product_total_after_discount + $delivery_charges;
    if (abs($calculated_total - $final_price) > 0.01) {
        // If there's a significant difference, use the calculated value
        $total_price = $calculated_total;
    } else {
        // Otherwise, use the stored price
        $total_price = $final_price;
    }
} else {
    // If no price is stored in the order, calculate it
    $original_price = $order['original_price'];
    $discount_percentage = $order['discount'];
    $quantity = $order['quantity'];
    
    // Calculate discount amount
    $discount_amount = $original_price * ($discount_percentage / 100);
    
    // Calculate price after discount
    $unit_price_after_discount = $original_price - $discount_amount;
    
    // Calculate total product price after discount
    $product_total_after_discount = $unit_price_after_discount * $quantity;
    
    // Calculate total product price before discount (for display)
    $product_total_before_discount = $original_price * $quantity;
    
    // Calculate total discount amount
    $total_discount_amount = $discount_amount * $quantity;
    
    // Delivery charges logic
    $delivery_type = $order['delivery_type'] ?? "Standard";
    $delivery_charges = 0;
    if ($delivery_type == "Standard") {
        $delivery_charges = 250;
    } elseif ($delivery_type == "Fast") {
        $delivery_charges = 500;
    }
    
    // Final total (after discount + delivery charges)
    $total_price = $product_total_after_discount + $delivery_charges;
}

// Calculate expected delivery date based on delivery type
if ($delivery_type == "Standard") {
    $delivery_days = 5;
} else { // Fast
    $delivery_days = 2;
}
$expected_delivery_date = date("d M Y", strtotime("+$delivery_days days", strtotime($order['created_at'])));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt - Slaymart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .receipt-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .receipt {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        
        .receipt::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .receipt-header {
            padding: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .logo {
            max-width: 120px;
            height: auto;
            margin-bottom: 1rem;
        }
        
        .confirmation-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--success-color);
            margin-bottom: 0.5rem;
        }
        
        .customer-greeting {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .confirmation-message {
            color: #6c757d;
            font-size: 0.95rem;
        }
        
        .order-details {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .detail-item {
            margin-bottom: 1rem;
        }
        
        .detail-label {
            font-size: 0.85rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .product-section {
            padding: 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .product-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .product-category {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .product-quantity {
            font-size: 0.9rem;
            color: var(--dark-color);
        }
        
        .product-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .billing-section {
            padding: 2rem;
        }
        
        .billing-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
        }
        
        .billing-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
        }
        
        .billing-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .billing-label {
            color: #6c757d;
        }
        
        .billing-value {
            font-weight: 600;
        }
        
        .billing-row.discount .billing-label {
            color: var(--success-color);
        }
        
        .billing-row.discount .billing-value {
            color: var(--success-color);
        }
        
        .billing-row.delivery .billing-label {
            color: var(--primary-color);
        }
        
        .billing-row.total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #eee;
            font-size: 1.1rem;
        }
        
        .billing-row.total .billing-label,
        .billing-row.total .billing-value {
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .delivery-info {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #eee;
        }
        
        .delivery-title {
            font-size: 1rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .delivery-date {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--success-color);
        }
        
        .delivery-note {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        .receipt-footer {
            padding: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .thanks {
            font-size: 1.1rem;
        }
        
        .thanks-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .thanks-subtitle {
            color: #6c757d;
        }
        
        .contact-info {
            text-align: right;
        }
        
        .contact-title {
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .contact-detail {
            color: #6c757d;
        }
        
        .back-button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
        }
        
        .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.4);
            color: white;
        }
        
        .qr-code {
            max-width: 120px;
            height: auto;
        }
        
        @media (max-width: 768px) {
            .receipt-footer {
                flex-direction: column;
                gap: 1.5rem;
                text-align: center;
            }
            
            .contact-info {
                text-align: center;
            }
            
            .billing-row {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
<div class="receipt-container">
    <div class="receipt">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <img src="../../images/logo/logo.png" alt="Slaymart Logo" class="logo">
            <h2 class="confirmation-title">
                <i class="bi bi-check-circle-fill me-2"></i> Your order is confirmed!
            </h2>
            <p class="customer-greeting">Hello <?= htmlspecialchars($order['fullname']) ?>,</p>
            <p class="confirmation-message">Your order has been confirmed and will be shipped soon</p>
        </div>
        
        <!-- Order Details -->
        <div class="order-details">
            <div class="row">
                <div class="col-md-3 col-6 mb-3">
                    <div class="detail-item">
                        <div class="detail-label">Order Date</div>
                        <div class="detail-value"><?= date("d M Y", strtotime($order['created_at'])) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="detail-item">
                        <div class="detail-label">Order Number</div>
                        <div class="detail-value">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="detail-item">
                        <div class="detail-label">Payment Method</div>
                        <div class="detail-value"><?= htmlspecialchars($order['payment_method']) ?></div>
                    </div>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <div class="detail-item">
                        <div class="detail-label">Shipping Address</div>
                        <div class="detail-value text-success"><?= htmlspecialchars($order['address']) ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Details -->
        <div class="product-section">
            <h4 class="mb-4">Product Details</h4>
            <div class="product-card">
                <div class="d-flex align-items-center">
                    <img src="../../images/uploads/<?= $order['product_image'] ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" class="product-image me-4">
                    <div class="flex-grow-1">
                        <h5 class="product-name"><?= htmlspecialchars($order['product_name']) ?></h5>
                        <p class="product-category">Category: <?= htmlspecialchars($order['product_category']) ?></p>
                        <p class="product-quantity">Quantity: <?= $quantity ?> pcs</p>
                    </div>
                    <div class="product-price">
                        PKR <?= number_format($unit_price_after_discount, 2) ?>
                        <?php if ($discount_percentage > 0): ?>
                        <small class="text-muted text-decoration-line-through ms-2">PKR <?= number_format($original_price, 2) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Billing Details -->
        <div class="billing-section">
            <div class="row">
                <div class="col-md-8">
                    <div class="billing-card">
                        <h4 class="billing-title">Order Summary</h4>
                        
                        <!-- Subtotal (without discount) -->
                        <div class="billing-row">
                            <span class="billing-label">Subtotal</span>
                            <span class="billing-value">PKR <?= number_format($product_total_before_discount, 2) ?></span>
                        </div>
                        
                        <!-- Discount Applied -->
                        <?php if ($discount_percentage > 0): ?>
                        <div class="billing-row discount">
                            <span class="billing-label">Discount (<?= $discount_percentage ?>%)</span>
                            <span class="billing-value">-PKR <?= number_format($total_discount_amount, 2) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Delivery Type -->
                        <div class="billing-row delivery">
                            <span class="billing-label">Delivery Type</span>
                            <span class="billing-value"><?= htmlspecialchars($delivery_type) ?> (PKR <?= number_format($delivery_charges, 2) ?>)</span>
                        </div>
                        
                        <!-- Final Total -->
                        <div class="billing-row total">
                            <span class="billing-label">Total</span>
                            <span class="billing-value">PKR <?= number_format($total_price, 2) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex justify-content-center align-items-center">
                    <img src="https://i.imgur.com/AXdWCWr.gif" alt="QR Code" class="qr-code">
                </div>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="delivery-info">
            <h5 class="delivery-title">Expected Delivery Date</h5>
            <p class="delivery-date"><?= $expected_delivery_date ?></p>
            <p class="delivery-note">We will be sending a shipping confirmation email when the item is shipped!</p>
        </div>
        
        <!-- Receipt Footer -->
        <div class="receipt-footer">
            <div class="thanks">
                <div class="thanks-title">Thanks for shopping</div>
                <div class="thanks-subtitle">Slaymart Team</div>
            </div>
            <div>
                <a href="javascript:history.back()" class="back-button">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
            </div>
            <div class="contact-info">
                <div class="contact-title">Need Help?</div>
                <div class="contact-detail">Call - +92 3108422790</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>