<?php
include "../config/db.php";

// ✅ Add to Cart
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity']++;
    } else {
        $sql = "SELECT * FROM products WHERE id=$id LIMIT 1";
        $result = mysqli_query($conn, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION['cart'][$id] = [
                "name" => $row['name'],
                "price" => $row['price'],
                "quantity" => 1,
                "image" => $row['image1']
            ];
        }
    }
    header("Location: index.php");
    exit;
}

// ✅ Decrease Quantity
if (isset($_GET['decrease'])) {
    $id = intval($_GET['decrease']);
    if (isset($_SESSION['cart'][$id])) {
        if ($_SESSION['cart'][$id]['quantity'] > 1) {
            $_SESSION['cart'][$id]['quantity']--;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    header("Location: index.php");
    exit;
}

// ✅ Remove Item
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: index.php");
    exit;
}

// ✅ Place Order (simple example - you can extend to save in DB)
if (isset($_POST['order_now'])) {
    if (!empty($_SESSION['cart'])) {
        // yahan tum apni orders table me save kar sakte ho
        unset($_SESSION['cart']); // cart empty
        $message = "✅ Your order has been placed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
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
            --green-color: #00c851;
            --red-color: #ff4444;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            min-height: 100vh;
        }
        
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .page-header {
            margin-bottom: 2rem;
            position: relative;
        }
        
        .page-title {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }
        
        .page-subtitle {
            color: #6c757d;
            font-weight: 400;
            font-size: 1.1rem;
        }
        
        .cart-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }
        
        .cart-items {
            flex: 1 1 70%;
            min-width: 300px;
        }
        
        .cart-summary {
            flex: 1 1 30%;
            min-width: 280px;
        }
        
        .cart-item {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .cart-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }
        
        .cart-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.12);
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .cart-item:hover .cart-item-image {
            transform: scale(1.05);
        }
        
        .cart-item-details h5 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--dark-color);
            font-size: 1.3rem;
        }
        
        .cart-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .cart-item-price i {
            margin-right: 8px;
            color: var(--primary-color);
        }
        
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 1.25rem 0;
        }
        
        .quantity-label {
            font-weight: 600;
            color: #6c757d;
            margin-right: 0.5rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.1rem;
            color: var(--dark-color);
        }
        
        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .quantity-btn:first-child {
            border-radius: 50px 0 0 50px;
        }
        
        .quantity-btn:last-child {
            border-radius: 0 50px 50px 0;
        }
        
        .quantity-display {
            min-width: 50px;
            text-align: center;
            font-weight: 700;
            font-size: 1.1rem;
            padding: 0 0.5rem;
        }
        
        .cart-item-total {
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.4rem;
            margin: 1rem 0;
            display: flex;
            align-items: center;
        }
        
        .cart-item-total i {
            margin-right: 8px;
        }
        
        .cart-item-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.25rem;
        }
        
        .btn-remove {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }
        
        .btn-remove:hover {
            background-color: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(220, 53, 69, 0.4);
        }
        
        .btn-order {
            background: linear-gradient(135deg, var(--success-color), #20c997);
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }
        
        .btn-order:hover {
            background: linear-gradient(135deg, #218838, #1e9b7c);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.4);
        }
        
        .summary-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 2rem;
            position: sticky;
            top: 1.5rem;
            overflow: hidden;
        }
        
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .summary-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.25rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .summary-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .summary-value {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .summary-total {
            font-size: 1.5rem;
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.4);
        }
        
        .checkout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.6);
        }
        
        .checkout-btn i {
            margin-right: 10px;
        }
        
        .empty-cart {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 4rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .empty-cart::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .empty-cart-icon {
            font-size: 5rem;
            color: #dee2e6;
            margin-bottom: 1.5rem;
        }
        
        .empty-cart-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark-color);
            font-size: 1.8rem;
        }
        
        .empty-cart-text {
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1.1rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .continue-shopping-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.4);
        }
        
        .continue-shopping-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.6);
        }
        
        .continue-shopping-btn i {
            margin-right: 10px;
        }
        
        .alert-custom {
            border-radius: 12px;
            border: none;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        
        .alert-success-custom {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-success-custom i {
            color: var(--success-color);
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .alert-warning-custom {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-color);
            text-decoration: none;
            margin-bottom: 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: rgba(106, 17, 203, 0.1);
        }
        
        .back-link:hover {
            color: var(--secondary-color);
            background: rgba(106, 17, 203, 0.2);
        }
        
        .back-link i {
            margin-right: 8px;
        }
        
        .cart-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .cart-icon-container {
            position: relative;
            display: inline-block;
        }
        
        .cart-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .header-cart {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                flex-direction: column;
            }
            
            .cart-items, .cart-summary {
                flex: 1 1 100%;
            }
            
            .summary-card {
                position: static;
                margin-top: 2rem;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .cart-item-image {
                width: 80px;
                height: 80px;
            }
            
            .cart-item-details h5 {
                font-size: 1.1rem;
            }
            
            .quantity-btn {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>
<div class="header-cart">
    <div class="cart-icon-container">
        <i class="bi bi-cart3 cart-icon"></i>
        <?php 
        $total_items = 0;
        if (!empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total_items += $item['quantity'];
            }
        }
        if ($total_items > 0): ?>
            <span class="cart-badge"><?php echo $total_items; ?></span>
        <?php endif; ?>
    </div>
</div>

<div class="main-container">
    <div class="container">
        <a href="../index.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Continue Shopping
        </a>
        
        <div class="page-header">
            <h1 class="page-title">Shopping Cart</h1>
            <p class="page-subtitle">Review your items and proceed to checkout</p>
        </div>
        
        <?php if (isset($message)) : ?>
            <div class="alert alert-custom alert-success-custom">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= $message ?></div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['cart'])): ?>
            <div class="cart-container">
                <div class="cart-items">
                    <?php 
                    $grandTotal = 0;
                    foreach ($_SESSION['cart'] as $id => $item): 
                        $total = $item['price'] * $item['quantity'];
                        $grandTotal += $total;
                    ?>
                    <div class="cart-item">
                        <div class="d-flex align-items-center">
                            <div class="me-4">
                                <img src="/ecommerce/images/uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="cart-item-image">
                            </div>
                            <div class="cart-item-details flex-grow-1">
                                <h5><?= $item['name'] ?></h5>
                                <div class="cart-item-price">
                                    <i class="fas fa-money-bill-wave"></i>
                                    PKR <?= number_format($item['price'], 2) ?>
                                </div>
                                
                                <div class="cart-item-quantity">
                                    <span class="quantity-label">Quantity:</span>
                                    <div class="quantity-control">
                                        <a href="?decrease=<?= $id ?>" class="quantity-btn">
                                            <i class="fas fa-minus"></i>
                                        </a>
                                        <div class="quantity-display"><?= $item['quantity'] ?></div>
                                        <a href="?add=<?= $id ?>" class="quantity-btn">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="cart-item-total">
                                    <i class="fas fa-calculator"></i>
                                    PKR <?= number_format($total, 2) ?>
                                </div>
                                
                                <div class="cart-item-actions">
                                    <a href="?remove=<?= $id ?>" class="btn-remove">
                                        <i class="fas fa-trash-alt me-2"></i> Remove
                                    </a>
                                    <a href="../product-detail/index.php?id=<?= $id ?>" class="btn-order">
                                        <i class="fas fa-shopping-bag me-2"></i> Order Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="summary-card">
                        <h4 class="summary-title">
                            <i class="fas fa-receipt"></i> Order Summary
                        </h4>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal</span>
                            <span class="summary-value">PKR <?= number_format($grandTotal, 2) ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Shipping</span>
                            <span class="summary-value">Free</span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Tax</span>
                            <span class="summary-value">PKR <?= number_format($grandTotal * 0.1, 2) ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Total</span>
                            <span class="summary-value summary-total">PKR <?= number_format($grandTotal * 1.1, 2) ?></span>
                        </div>
                        
                        <form method="post">
                            <button type="submit" name="order_now" class="checkout-btn">
                                <i class="fas fa-lock"></i> Secure Checkout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3 class="empty-cart-title">Your Cart is Empty</h3>
                <p class="empty-cart-text">Looks like you haven't added any items to your cart yet. Start shopping to fill it up!</p>
                <a href="../index.php" class="continue-shopping-btn">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>