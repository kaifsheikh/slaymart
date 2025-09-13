<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();
// ✅ Check login
if (!isset($_SESSION['user_id'])) {
  header("Location: ../users/login.php");
  exit();
}
// ✅ Get product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
  if (isset($_SESSION['last_product_id'])) {
    $product_id = (int) $_SESSION['last_product_id'];
  } else {
    die("Product ID is missing.");
  }
} else {
  $product_id = (int) $_GET['id'];
  $_SESSION['last_product_id'] = $product_id;
}
// ✅ Fetch product details
$stmt = $conn->prepare("
    SELECT id, name, category, price, discount, type, stock_status 
    FROM products 
    WHERE id = ? 
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
$product = $res->fetch_assoc();
$stmt->close();
if (!$product) {
  die("Product not found.");
}
// ✅ Normalize values
$price = (float)($product['price'] ?? 0.0);
$discount = (float)($product['discount'] ?? 0.0);
if ($discount < 0) $discount = 0;
$unit_price = max(0, $price - ($price * $discount / 100));
// ✅ Fetch ALL product images
$images = [];
$imgQuery = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC");
$imgQuery->bind_param("i", $product_id);
$imgQuery->execute();
$imgRes = $imgQuery->get_result();
while ($imgRow = $imgRes->fetch_assoc()) {
  $images[] = $imgRow['image'];
}
$imgQuery->close();
// ✅ Fallback if no images
if (empty($images)) {
  $images[] = 'placeholder.png';
}
// ✅ Fetch product colors (JOIN colors table)
$colors = [];
$colorQuery = $conn->prepare("
    SELECT c.id, c.name 
    FROM product_colors pc 
    INNER JOIN colors c ON pc.color_id = c.id 
    WHERE pc.product_id = ?
");
$colorQuery->bind_param("i", $product_id);
$colorQuery->execute();
$colorRes = $colorQuery->get_result();
while ($row = $colorRes->fetch_assoc()) {
  $colors[] = $row;
}
$colorQuery->close();
// ✅ Fetch product sizes (JOIN sizes table)
$sizes = [];
$sizeQuery = $conn->prepare("
    SELECT s.id, s.name 
    FROM product_sizes ps 
    INNER JOIN sizes s ON ps.size_id = s.id 
    WHERE ps.product_id = ?
");
$sizeQuery->bind_param("i", $product_id);
$sizeQuery->execute();
$sizeRes = $sizeQuery->get_result();
while ($row = $sizeRes->fetch_assoc()) {
  $sizes[] = $row;
}
$sizeQuery->close();
// ✅ Delivery
$default_delivery = 250;
$initial_total = $unit_price + $default_delivery;
?>
<!doctype html>
<html lang="hi">
<head>
  <meta charset="utf-8">
  <title>Slaymart Checkout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
      --primary-color: #6366f1;
      --secondary-color: #8b5cf6;
      --dark-color: #1e293b;
      --light-color: #f8fafc;
      --success-color: #10b981;
      --info-color: #0ea5e9;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --gray-color: #64748b;
      --light-gray: #f1f5f9;
      --border-radius: 16px;
      --box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      font-family: 'Poppins', sans-serif;
      color: var(--dark-color);
      min-height: 100vh;
      padding: 30px 0;
    }
    .checkout-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }
    .page-header {
      background: white;
      border-radius: var(--border-radius);
      padding: 25px 30px;
      margin-bottom: 30px;
      box-shadow: var(--box-shadow);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .page-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark-color);
      margin: 0;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .page-title i {
      color: var(--primary-color);
      font-size: 1.5rem;
    }
    .back-btn {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 10px;
      font-weight: 500;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .back-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
      color: white;
    }
    .card {
      border-radius: var(--border-radius);
      border: none;
      box-shadow: var(--box-shadow);
      margin-bottom: 30px;
      overflow: hidden;
      transition: var(--transition);
      border: 1px solid rgba(0, 0, 0, 0.05);
    }
    .card:hover {
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
    }
    .card-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 20px 25px;
      font-weight: 600;
      border: none;
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .card-body {
      padding: 30px;
    }
    .summary-img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 12px;
      transition: var(--transition);
      cursor: pointer;
      border: 2px solid transparent;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .summary-img:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    .summary-img.selected {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
    }
    .product-details {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 25px;
    }
    .product-info h5 {
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--dark-color);
      font-size: 1.25rem;
    }
    .product-info .price {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--primary-color);
    }
    .product-info .original-price {
      text-decoration: line-through;
      color: var(--gray-color);
      font-size: 0.95rem;
      margin-left: 10px;
    }
    .discount-badge {
      background: var(--success-color);
      color: white;
      font-size: 0.75rem;
      padding: 3px 8px;
      border-radius: 20px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .order-summary {
      background: var(--light-color);
      border-radius: 12px;
      padding: 20px;
      margin-top: 20px;
    }
    .summary-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 0.95rem;
      color: var(--dark-color);
    }
    .summary-row:last-child {
      margin-bottom: 0;
      padding-top: 15px;
      border-top: 1px solid rgba(0, 0, 0, 0.05);
      font-weight: 700;
      font-size: 1.2rem;
      color: var(--dark-color);
    }
    .form-label {
      font-weight: 600;
      font-size: 0.95rem;
      margin-bottom: 8px;
      color: var(--dark-color);
    }
    .form-control,
    .form-select {
      border-radius: 10px;
      border: 1px solid #e2e8f0;
      padding: 12px 15px;
      font-size: 0.95rem;
      transition: var(--transition);
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .form-control:focus,
    .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
      background-color: white;
    }
    .btn {
      border-radius: 10px;
      font-weight: 600;
      padding: 14px 24px;
      transition: var(--transition);
      font-size: 0.95rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .btn-success {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      color: white;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }
    .btn-success:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.4);
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
    }
    .btn-outline-secondary {
      border: 1px solid #e2e8f0;
      color: var(--gray-color);
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .btn-outline-secondary:hover {
      background-color: var(--light-gray);
      color: var(--dark-color);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .image-selector {
      display: flex;
      gap: 12px;
      margin-top: 20px;
      flex-wrap: wrap;
    }
    .quantity-control {
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .quantity-btn {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      border: 1px solid #e2e8f0;
      background: white;
      color: var(--dark-color);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .quantity-btn:hover {
      background: var(--light-gray);
      border-color: var(--primary-color);
      color: var(--primary-color);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .quantity-input {
      width: 70px;
      text-align: center;
      border-radius: 10px;
      font-weight: 600;
    }
    .delivery-options {
      display: flex;
      gap: 15px;
      margin-top: 15px;
      flex-wrap: wrap;
    }
    .delivery-option {
      flex: 1;
      min-width: 140px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .delivery-option:hover {
      border-color: var(--primary-color);
      background: rgba(99, 102, 241, 0.05);
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .delivery-option.selected {
      border-color: var(--primary-color);
      background: rgba(99, 102, 241, 0.1);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.2);
    }
    .delivery-option h6 {
      font-weight: 600;
      margin-bottom: 5px;
      color: var(--dark-color);
      font-size: 1rem;
    }
    .delivery-option p {
      margin-bottom: 5px;
      font-size: 0.85rem;
      color: var(--gray-color);
    }
    .delivery-option .price {
      color: var(--primary-color);
      font-weight: 700;
      font-size: 1rem;
    }
    .payment-methods {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      margin-top: 15px;
    }
    .payment-method {
      flex: 1;
      min-width: 130px;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 18px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background-color: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .payment-method:hover {
      border-color: var(--primary-color);
      background: rgba(99, 102, 241, 0.05);
      transform: translateY(-3px);
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }
    .payment-method.selected {
      border-color: var(--primary-color);
      background: rgba(99, 102, 241, 0.1);
      box-shadow: 0 6px 16px rgba(99, 102, 241, 0.2);
    }
    .payment-method i {
      font-size: 1.8rem;
      margin-bottom: 8px;
      color: var(--primary-color);
    }
    .payment-method div {
      font-weight: 500;
      font-size: 0.9rem;
      color: var(--dark-color);
    }
    .total-section {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      border-radius: 12px;
      padding: 25px;
      margin-top: 25px;
      box-shadow: 0 8px 20px rgba(99, 102, 241, 0.25);
    }
    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 0.95rem;
    }
    .total-row:last-child {
      margin-bottom: 0;
      padding-top: 15px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
      font-size: 1.3rem;
      font-weight: 700;
    }
    .form-check {
      margin-bottom: 15px;
    }
    .form-check-input:checked {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    .form-check-input:focus {
      box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.25);
    }
    .form-check-label a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
    }
    .form-check-label a:hover {
      text-decoration: underline;
    }
    .section-title {
      font-weight: 600;
      margin-bottom: 15px;
      color: var(--dark-color);
      font-size: 1.1rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-title i {
      color: var(--primary-color);
    }
    
    /* Exclusive Product Styles */
    .exclusive-product {
      background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
      border: 1px solid #bae6fd;
    }
    .exclusive-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }
    .exclusive-badge {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      font-size: 0.75rem;
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .stock-status {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
    }
    .stock-status i {
      font-size: 1.2rem;
    }
    .stock-status.in-stock {
      color: var(--success-color);
    }
    .stock-status.out-of-stock {
      color: var(--danger-color);
    }
    
    /* Color and Size Selectors */
    .color-size-selector {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }
    .color-selector, .size-selector {
      flex: 1;
    }
    .color-option, .size-option {
      display: inline-block;
      padding: 8px 15px;
      margin: 5px;
      border-radius: 20px;
      background: white;
      border: 1px solid #e2e8f0;
      cursor: pointer;
      transition: var(--transition);
      font-size: 0.9rem;
      font-weight: 500;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    .color-option:hover, .size-option:hover {
      background: var(--light-gray);
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    .color-option.selected, .size-option.selected {
      background: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
      box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
    }
    
    /* Responsive Styles */
    @media (max-width: 991px) {
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      .page-title {
        font-size: 1.5rem;
      }
      .card-body {
        padding: 25px;
      }
    }
    @media (max-width: 767px) {
      body {
        padding: 10px 0;
      }
      .checkout-container {
        padding: 0 10px;
      }
      .card-body {
        padding: 20px;
      }
      .delivery-options {
        flex-direction: column;
      }
      .delivery-option {
        width: 100%;
      }
      .payment-methods {
        justify-content: space-between;
      }
      .payment-method {
        min-width: calc(50% - 6px);
      }
      .product-details {
        flex-direction: column;
        text-align: center;
      }
      .image-selector {
        justify-content: center;
      }
      .quantity-control {
        justify-content: center;
      }
      .color-size-selector {
        flex-direction: column;
      }
    }
    @media (max-width: 576px) {
      .page-title {
        font-size: 1.3rem;
      }
      .card-header {
        padding: 15px 20px;
        font-size: 1rem;
      }
      .card-body {
        padding: 15px;
      }
      .payment-method {
        min-width: 100%;
      }
      .total-section {
        padding: 20px;
      }
    }
    
    /* Animation for page load */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .card {
      animation: fadeIn 0.5s ease forwards;
    }
    .card:nth-child(1) {
      animation-delay: 0.1s;
    }
    .card:nth-child(2) {
      animation-delay: 0.2s;
    }
  </style>
</head>
<body>
  <div class="checkout-container">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-shopping-cart"></i> Checkout</h1>
      <a href="javascript:history.back()" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Shopping
      </a>
    </div>
    
    <?php if ($product['type'] === 'exclusive'): ?>
      <div class="exclusive-product">
        <div class="exclusive-header">
          <span class="exclusive-badge">Exclusive Product</span>
          <div class="stock-status <?php echo $product['stock_status'] === 'in' ? 'in-stock' : 'out-of-stock'; ?>">
            <i class="fas <?php echo $product['stock_status'] === 'in' ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
            <span><?php echo $product['stock_status'] === 'in' ? 'In Stock' : 'Out of Stock'; ?></span>
          </div>
        </div>
        
        <div class="color-size-selector">
          <div class="color-selector">
            <label class="form-label">Color</label>
            <div class="color-options">
              <?php foreach ($colors as $c): ?>
                <div class="color-option" data-id="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
          
          <div class="size-selector">
            <label class="form-label">Size</label>
            <div class="size-options">
              <?php foreach ($sizes as $s): ?>
                <div class="size-option" data-id="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <form action="place_order.php" method="post" class="needs-validation" novalidate>
      <!-- hidden pricing fields -->
      <input type="hidden" name="product_id" value="<?= (int)$product_id ?>">
      <input type="hidden" id="unitPrice" value="<?= htmlspecialchars($unit_price, ENT_QUOTES) ?>">
      <input type="hidden" id="priceField" name="price" value="<?= htmlspecialchars($initial_total, ENT_QUOTES) ?>">
      <input type="hidden" id="deliveryChargesField" name="delivery_charges" value="<?= $default_delivery ?>">
      <input type="hidden" id="delivery_type" name="delivery_type" value="Standard">
      <input type="hidden" name="selected_image" id="selectedImage" value="<?= htmlspecialchars($images[0]) ?>">
      <input type="hidden" name="payment_method" id="payment_method" value="COD">
      <input type="hidden" name="product_type" value="<?= htmlspecialchars($product['type']) ?>">
      <!-- Single set of hidden fields for color and size -->
      <input type="hidden" name="color_id" id="colorId" value="<?= !empty($colors) ? $colors[0]['id'] : '0' ?>">
      <input type="hidden" name="size_id" id="sizeId" value="<?= !empty($sizes) ? $sizes[0]['id'] : '0' ?>">
      
      <div class="row g-4">
        <!-- Product Summary -->
        <div class="col-lg-5">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><i class="fas fa-shopping-bag"></i> Order Summary</h5>
            </div>
            <div class="card-body">
              <div class="product-details">
                <img src="../images/uploads/<?= htmlspecialchars($images[0]) ?>" class="summary-img selected" id="mainProductImage" alt="Product Image">
                <div class="product-info">
                  <h5><?= htmlspecialchars($product['name']) ?></h5>
                  <div>
                    <span class="price">PKR <?= number_format($unit_price, 0) ?></span>
                    <?php if ($discount > 0): ?>
                      <span class="original-price">PKR <?= number_format($price, 0) ?></span>
                      <span class="discount-badge"><?= $discount ?>% OFF</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              
              <!-- Image Selector -->
              <div class="section-title">
                <i class="fas fa-images"></i> Select Image
              </div>
              <div class="image-selector">
                <?php foreach ($images as $index => $img): ?>
                  <img src="../images/uploads/<?= htmlspecialchars($img) ?>"
                    class="summary-img selectable <?= $index === 0 ? 'selected' : '' ?>"
                    data-img="<?= htmlspecialchars($img) ?>"
                    alt="Product Image <?= $index + 1 ?>">
                <?php endforeach; ?>
              </div>
              
              <!-- Quantity Selector -->
              <div class="mb-3 mt-4">
                <div class="section-title">
                  <i class="fas fa-sort-numeric-up"></i> Quantity
                </div>
                <div class="quantity-control">
                  <button type="button" class="quantity-btn" id="decreaseQty"><i class="fas fa-minus"></i></button>
                  <input type="number" class="form-control quantity-input" name="quantity" id="quantity" value="1" min="1" required>
                  <button type="button" class="quantity-btn" id="increaseQty"><i class="fas fa-plus"></i></button>
                </div>
              </div>
              
              <!-- Delivery Options -->
              <div class="mb-3">
                <div class="section-title">
                  <i class="fas fa-shipping-fast"></i> Delivery Type
                </div>
                <div class="delivery-options">
                  <div class="delivery-option selected" data-value="Standard" data-charge="250">
                    <h6>Standard</h6>
                    <p>3-5 working days</p>
                    <p class="price">PKR 250</p>
                  </div>
                  <div class="delivery-option" data-value="Fast" data-charge="500">
                    <h6>Fast</h6>
                    <p>1-2 working days</p>
                    <p class="price">PKR 500</p>
                  </div>
                </div>
              </div>
              
              <!-- Order Total -->
              <div class="total-section">
                <div class="total-row"><span>Subtotal</span><span id="subtotal">PKR <?= number_format($unit_price, 0) ?></span></div>
                <div class="total-row"><span>Delivery</span><span id="deliveryCharge">PKR <?= $default_delivery ?></span></div>
                <div class="total-row"><span>Total</span><span id="totalPrice">PKR <?= number_format($initial_total, 0) ?></span></div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Customer Details -->
        <div class="col-lg-7">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><i class="fas fa-user"></i> Customer Details</h5>
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-12">
                  <label for="fullname" class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="fullname" id="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" id="email" placeholder="Enter your email" required>
                </div>
                <div class="col-md-6">
                  <label for="phone" class="form-label">Phone</label>
                  <input type="text" class="form-control" name="phone" id="phone" placeholder="Enter your phone number" required>
                </div>
                <div class="col-12">
                  <label for="address" class="form-label">Address</label>
                  <textarea class="form-control" name="address" id="address" rows="3" placeholder="Enter your complete address" required></textarea>
                </div>
                
                <!-- Payment Methods -->
                <div class="col-12">
                  <div class="section-title">
                    <i class="fas fa-credit-card"></i> Payment Method
                  </div>
                  <div class="payment-methods">
                    <div class="payment-method selected" data-value="COD">
                      <i class="fas fa-money-bill-wave"></i>
                      <div>Cash on Delivery</div>
                    </div>
                    <div class="payment-method" data-value="EasyPaisa">
                      <i class="fas fa-mobile-alt"></i>
                      <div>EasyPaisa</div>
                    </div>
                    <div class="payment-method" data-value="Bank Transfer">
                      <i class="fas fa-university"></i>
                      <div>Bank Transfer</div>
                    </div>
                  </div>
                </div>
                
                <div class="col-12">
                  <label for="note" class="form-label">Delivery Note (optional)</label>
                  <input type="text" class="form-control" name="note" id="note" placeholder="e.g. Leave at gate">
                </div>
                
                <!-- Terms and Conditions -->
                <div class="col-12">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="termsCheck" required>
                    <label class="form-check-label" for="termsCheck">
                      I agree to the <a href="#">Terms and Conditions</a>
                    </label>
                  </div>
                </div>
                
                <!-- Buttons -->
                <div class="col-12 mt-4">
                  <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check-circle"></i> Place Order
                  </button>
                  <a href="javascript:history.back()" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="fas fa-arrow-left"></i> Back to Shopping
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
  
<script>
    const quantityInput = document.getElementById("quantity");
    const decreaseBtn = document.getElementById("decreaseQty");
    const increaseBtn = document.getElementById("increaseQty");
    const deliveryOptions = document.querySelectorAll(".delivery-option");
    const paymentMethods = document.querySelectorAll(".payment-method");
    const imageOptions = document.querySelectorAll(".summary-img.selectable");
    const mainProductImage = document.getElementById("mainProductImage");
    const selectedImageInput = document.getElementById("selectedImage");
    const deliveryChargeRow = document.getElementById("deliveryCharge");
    const deliveryChargesField = document.getElementById("deliveryChargesField");
    const totalPrice = document.getElementById("totalPrice");
    const subtotal = document.getElementById("subtotal");
    const priceField = document.getElementById("priceField");
    const paymentMethodField = document.getElementById("payment_method");
    const unitPrice = parseFloat(document.getElementById("unitPrice").value);
    const productType = document.querySelector('input[name="product_type"]').value;
    let deliveryCharge = 250;
    
    function updateTotal() {
      let qty = parseInt(quantityInput.value);
      if (isNaN(qty) || qty < 1) qty = 1;
      const subtotalAmount = qty * unitPrice;
      subtotal.textContent = "PKR " + subtotalAmount.toFixed(0);
      const total = subtotalAmount + deliveryCharge;
      totalPrice.textContent = "PKR " + total.toFixed(0);
      priceField.value = total.toFixed(0);
      deliveryChargeRow.textContent = "PKR " + deliveryCharge;
      deliveryChargesField.value = deliveryCharge;
    }
    
    decreaseBtn.addEventListener("click", () => {
      if (quantityInput.value > 1) {
        quantityInput.value--;
        updateTotal();
      }
    });
    
    increaseBtn.addEventListener("click", () => {
      quantityInput.value++;
      updateTotal();
    });
    
    quantityInput.addEventListener("input", updateTotal);
    
    deliveryOptions.forEach(opt => {
      opt.addEventListener("click", function() {
        deliveryOptions.forEach(o => o.classList.remove("selected"));
        this.classList.add("selected");
        deliveryCharge = parseFloat(this.dataset.charge) || 0;
        document.getElementById("delivery_type").value = this.dataset.value;
        updateTotal();
      });
    });
    
    paymentMethods.forEach(m => {
      m.addEventListener("click", function() {
        paymentMethods.forEach(x => x.classList.remove("selected"));
        this.classList.add("selected");
        paymentMethodField.value = this.dataset.value;
      });
    });
    
    imageOptions.forEach(img => {
      img.addEventListener("click", function() {
        imageOptions.forEach(i => i.classList.remove("selected"));
        this.classList.add("selected");
        selectedImageInput.value = this.dataset.img;
        mainProductImage.src = this.src;
      });
    });
    
    // Handle color and size selection for exclusive products
    if (productType === 'exclusive') {
      const colorOptions = document.querySelectorAll(".color-option");
      const colorIdInput = document.getElementById("colorId");
      const sizeOptions = document.querySelectorAll(".size-option");
      const sizeIdInput = document.getElementById("sizeId");
      
      // Select first color and size by default
      if (colorOptions.length > 0) {
        colorOptions[0].classList.add("selected");
        colorIdInput.value = colorOptions[0].dataset.id;
      }
      
      if (sizeOptions.length > 0) {
        sizeOptions[0].classList.add("selected");
        sizeIdInput.value = sizeOptions[0].dataset.id;
      }
      
      // Handle color selection
      colorOptions.forEach(opt => {
        opt.addEventListener("click", function() {
          colorOptions.forEach(o => o.classList.remove("selected"));
          this.classList.add("selected");
          colorIdInput.value = this.dataset.id;
          console.log("Color selected:", this.dataset.id); // Debug
        });
      });
      
      // Handle size selection
      sizeOptions.forEach(opt => {
        opt.addEventListener("click", function() {
          sizeOptions.forEach(o => o.classList.remove("selected"));
          this.classList.add("selected");
          sizeIdInput.value = this.dataset.id;
          console.log("Size selected:", this.dataset.id); // Debug
        });
      });
      
      // Add validation to ensure user selects both color and size
      document.querySelector('form').addEventListener('submit', function(e) {
        if (!colorIdInput.value || colorIdInput.value === '0') {
          e.preventDefault();
          alert('Please select a color for this exclusive product.');
          return false;
        }
        
        if (!sizeIdInput.value || sizeIdInput.value === '0') {
          e.preventDefault();
          alert('Please select a size for this exclusive product.');
          return false;
        }
        
        console.log("Submitting form with color:", colorIdInput.value, "size:", sizeIdInput.value); // Debug
      });
    }
    
    // Initialize total
    updateTotal();
    
    // Debug: Log initial values
    console.log("Product type:", productType);
    if (productType === 'exclusive') {
      console.log("Initial color ID:", document.getElementById("colorId").value);
      console.log("Initial size ID:", document.getElementById("sizeId").value);
    }
  </script>
</body>
</html>