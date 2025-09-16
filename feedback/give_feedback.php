<?php
include("../config/db.php");
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ User login check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../users/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// ✅ Order ID from GET
$order_id = $_GET['order_id'] ?? 0;

// ✅ Check: order exist karta hai aur user ka hai
$sqlCheck = "SELECT id, product_id, selected_image FROM orders WHERE id = ? AND user_id = ? LIMIT 1";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $order_id, $user_id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows === 0) {
    header("Location: ../users/my_orders.php?msg=Invalid Order");
    exit();
}
$order = $resultCheck->fetch_assoc();
$product_id = $order['product_id'];
$selected_image = $order['selected_image'] ?? null;

// ✅ Check: kya review already diya gaya hai?
$sqlReviewCheck = "SELECT id FROM reviews WHERE order_id = ? AND user_id = ? LIMIT 1";
$stmtReview = $conn->prepare($sqlReviewCheck);
$stmtReview->bind_param("ii", $order_id, $user_id);
$stmtReview->execute();
$resultReview = $stmtReview->get_result();
if ($resultReview->num_rows > 0) {
    header("Location: ../users/my_orders.php?msg=Feedback Already Submitted");
    exit();
}

// ✅ Agar form submit hua
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = intval($_POST['rating']);
    $feedback = trim($_POST['feedback']);

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a valid rating (1-5).";
    } elseif (empty($feedback)) {
        $error = "Feedback cannot be empty.";
    } else {
        $sqlInsert = "INSERT INTO reviews (order_id, product_id, user_id, rating, feedback, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sqlInsert);
        $stmt->bind_param("iiiis", $order_id, $product_id, $user_id, $rating, $feedback);
        if ($stmt->execute()) {
            header("Location: ../users/my_orders.php?msg=Feedback Submitted Successfully");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
    }
}

// ✅ Fetch product details
$productQuery = "SELECT name FROM products WHERE id = ?";
$productStmt = $conn->prepare($productQuery);
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();
$product = $productResult->fetch_assoc();

// ✅ Fallback: agar order.selected_image empty hai to product_images se lo
if (!empty($selected_image)) {
    $product_img = $selected_image;
} else {
    $imgRes = mysqli_query($conn, "SELECT image FROM product_images WHERE product_id = {$product_id} ORDER BY id ASC LIMIT 1");
    $imgRow = mysqli_fetch_assoc($imgRes);
    $product_img = $imgRow['image'] ?? "no-image.png";
}

// ✅ Fetch order details
$orderQuery = "SELECT created_at FROM orders WHERE id = ?";
$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("i", $order_id);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();
$orderDetails = $orderResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - Slaymart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
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
        
        .feedback-container {
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
        
        /* Feedback Card */
        .feedback-card {
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--amazon-border);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        /* Feedback Header */
        .feedback-header {
            padding: 20px;
            text-align: center;
            background: var(--amazon-light-gray);
            border-bottom: 1px solid var(--amazon-border);
        }
        
        .feedback-title {
            font-size: 24px;
            font-weight: 400;
            color: var(--amazon-text);
            margin-bottom: 10px;
        }
        
        .feedback-subtitle {
            color: var(--amazon-light-text);
            font-size: 14px;
        }
        
        /* Feedback Body */
        .feedback-body {
            padding: 30px;
        }
        
        /* Product Info */
        .product-info {
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            border: 1px solid var(--amazon-border);
        }
        
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: contain;
            border: 1px solid var(--amazon-border);
            border-radius: var(--border-radius);
            padding: 5px;
            background: white;
            margin-right: 20px;
        }
        
        .product-details h5 {
            font-weight: 400;
            font-size: 18px;
            margin-bottom: 8px;
            color: var(--amazon-text);
        }
        
        .product-details p {
            color: var(--amazon-light-text);
            font-size: 14px;
            margin-bottom: 0;
        }
        
        .order-info {
            margin-left: auto;
            text-align: right;
        }
        
        .order-id {
            font-weight: 500;
            color: var(--amazon-text);
            font-size: 16px;
        }
        
        .order-date {
            color: var(--amazon-light-text);
            font-size: 14px;
        }
        
        /* Rating Section */
        .rating-section {
            margin-bottom: 30px;
        }
        
        .rating-label {
            font-weight: 500;
            font-size: 16px;
            margin-bottom: 15px;
            display: block;
        }
        
        .star-rating-container {
            display: flex;
            justify-content: center;
            margin-bottom: 10px;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse; /* This makes the stars fill from right to left */
            gap: 5px;
            font-size: 30px;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            color: var(--amazon-border);
            transition: var(--transition);
        }
        
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: var(--amazon-star);
        }
        
        .rating-text {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
            color: var(--amazon-light-text);
        }
        
        /* Feedback Section */
        .feedback-section {
            margin-bottom: 30px;
        }
        
        .feedback-label {
            font-weight: 500;
            font-size: 16px;
            margin-bottom: 15px;
            display: block;
        }
        
        .feedback-textarea {
            border: 1px solid var(--amazon-border);
            border-radius: var(--border-radius);
            padding: 12px;
            font-size: 14px;
            transition: var(--transition);
            resize: none;
            width: 100%;
        }
        
        .feedback-textarea:focus {
            border-color: var(--amazon-orange);
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
            outline: none;
        }
        
        /* Alert Messages */
        .alert-custom {
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            border: 1px solid var(--amazon-border);
        }
        
        .alert-success-custom {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
        }
        
        .alert-danger-custom {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
        }
        
        .alert-icon {
            font-size: 20px;
            margin-right: 15px;
        }
        
        /* Actions */
        .feedback-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .btn-back {
            background: white;
            color: var(--amazon-text);
            border: 1px solid var(--amazon-border);
            border-radius: var(--border-radius);
            padding: 12px 20px;
            font-weight: 400;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .btn-back:hover {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
        }
        
        .btn-submit {
            background: var(--amazon-orange);
            color: var(--amazon-blue);
            border: none;
            border-radius: var(--border-radius);
            padding: 12px 20px;
            font-weight: 400;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .btn-submit:hover {
            background: var(--amazon-light-orange);
        }
        
        .btn-back i, .btn-submit i {
            margin-right: 8px;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .feedback-title {
                font-size: 20px;
            }
            
            .star-rating {
                font-size: 24px;
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .order-info {
                margin-left: 0;
                margin-top: 15px;
                text-align: center;
            }
            
            .feedback-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn-back, .btn-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="feedback-container">
            <h1 class="page-title">Give Feedback</h1>
        </div>
    </div>
    
    <div class="feedback-container">
        <div class="feedback-card">
            <!-- Alert Messages -->
            <?php if (!empty($_GET['msg'])): ?>
                <div class="alert-custom alert-success-custom">
                    <i class="fas fa-check-circle alert-icon"></i> <?= htmlspecialchars($_GET['msg']) ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert-custom alert-danger-custom">
                    <i class="fas fa-exclamation-triangle alert-icon"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Feedback Header -->
            <div class="feedback-header">
                <h2 class="feedback-title">Share Your Experience</h2>
                <p class="feedback-subtitle">Your feedback helps us improve our products and services</p>
            </div>

            <!-- Feedback Body -->
            <div class="feedback-body">
                <!-- Product and Order Info -->
                <div class="product-info">
                    <img src="../images/uploads/<?= htmlspecialchars($product_img) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image">
                    <div class="product-details">
                        <h5><?= htmlspecialchars($product['name']) ?></h5>
                        <p>Order #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?></p>
                    </div>
                    <div class="order-info">
                        <div class="order-id"><?= date("d M Y", strtotime($orderDetails['created_at'])) ?></div>
                    </div>
                </div>

                <!-- Feedback Form -->
                <form method="POST">
                    <!-- Rating -->
                    <div class="rating-section">
                        <label class="rating-label">How would you rate this product?</label>
                        <div class="star-rating-container">
                            <div class="star-rating">
                                <?php for ($i=5; $i>=1; $i--): ?>
                                    <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                                    <label for="star<?= $i ?>"><i class="fas fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="rating-text">
                            <span>Poor</span>
                            <span>Excellent</span>
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div class="feedback-section">
                        <label class="feedback-label">Tell us more about your experience</label>
                        <textarea name="feedback" class="feedback-textarea" rows="4" placeholder="What did you like or dislike about this product?" required></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="feedback-actions">
                        <a href="../users/my_orders.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit Feedback
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>