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
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --star-color: #f5c518;
            --star-empty: #e0e0e0;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .feedback-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .feedback-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }
        
        .feedback-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }
        
        .feedback-header {
            padding: 2.5rem 2rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(106, 17, 203, 0.05) 0%, rgba(37, 117, 252, 0.05) 100%);
        }
        
        .feedback-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .feedback-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .feedback-body {
            padding: 2rem;
        }
        
        .rating-section {
            margin-bottom: 2.5rem;
        }
        
        .rating-label {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: block;
            text-align: center;
        }
        
        .star-rating-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .star-rating {
            display: flex;
            flex-direction: row-reverse; /* This makes the stars fill from right to left */
            gap: 5px;
            font-size: 2.5rem;
        }
        
        .star-rating input {
            display: none;
        }
        
        .star-rating label {
            cursor: pointer;
            color: var(--star-empty);
            transition: all 0.2s ease;
        }
        
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: var(--star-color);
            transform: scale(1.1);
        }
        
        .rating-text {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .feedback-section {
            margin-bottom: 2.5rem;
        }
        
        .feedback-label {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .feedback-textarea {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            resize: none;
        }
        
        .feedback-textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(106, 17, 203, 0.25);
            outline: none;
        }
        
        .feedback-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 2rem;
        }
        
        .btn-back {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .btn-back:hover {
            background: rgba(106, 17, 203, 0.1);
            color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(106, 17, 203, 0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(106, 17, 203, 0.4);
        }
        
        .btn-submit i, .btn-back i {
            margin-right: 0.5rem;
        }
        
        .alert-custom {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .alert-success-custom {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger-custom {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
        }
        
        .product-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 1.5rem;
        }
        
        .product-details h5 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .product-details p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .order-info {
            margin-left: auto;
            text-align: right;
        }
        
        .order-id {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .feedback-title {
                font-size: 1.5rem;
            }
            
            .star-rating {
                font-size: 2rem;
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
            }
            
            .product-image {
                margin-right: 0;
                margin-bottom: 1rem;
            }
            
            .order-info {
                margin-left: 0;
                margin-top: 1rem;
                text-align: center;
            }
            
            .feedback-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .btn-back, .btn-submit {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="container my-5">
    <div class="card shadow-lg p-4">
        <!-- ✅ Alert Messages -->
        <?php if (!empty($_GET['msg'])): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($_GET['msg']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Feedback Header -->
        <div class="text-center mb-4">
            <h2 class="fw-bold">Share Your Experience</h2>
            <p class="text-muted">Your feedback helps us improve our products and services</p>
        </div>

        <!-- ✅ Product and Order Info -->
        <div class="d-flex align-items-center mb-4">
            <img src="../images/uploads/<?= htmlspecialchars($product_img) ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>" 
                 class="img-thumbnail me-3" style="width:100px; height:100px; object-fit:cover;">
            <div>
                <h5 class="mb-1"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="mb-0">Order #<?= str_pad($order_id, 6, '0', STR_PAD_LEFT) ?> • 
                   <?= date("d M Y", strtotime($orderDetails['created_at'])) ?></p>
            </div>
        </div>

        <!-- ✅ Feedback Form -->
        <form method="POST">
            <!-- Rating -->
            <div class="mb-3">
                <label class="form-label fw-semibold">How would you rate this product?</label>
                <div class="d-flex gap-2 fs-4 text-warning">
                    <?php for ($i=5; $i>=1; $i--): ?>
                        <input type="radio" class="btn-check" name="rating" id="star<?= $i ?>" value="<?= $i ?>" required>
                        <label class="btn btn-outline-warning" for="star<?= $i ?>"><i class="fa-solid fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Feedback -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Tell us more about your experience</label>
                <textarea name="feedback" class="form-control" rows="4" placeholder="What did you like or dislike about this product?" required></textarea>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between">
                <a href="../users/my_orders.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Orders
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Submit Feedback
                </button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>