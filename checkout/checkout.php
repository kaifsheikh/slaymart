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
      --border-radius: 3px;
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
    
    .checkout-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    .page-header {
      background: var(--amazon-blue);
      color: white;
      padding: 12px 0;
      margin-bottom: 20px;
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 3px rgba(0,0,0,0.1);
    }
    
    .page-title {
      font-size: 28px;
      font-weight: 400;
      margin: 0;
    }
    
    .back-btn {
      background: var(--amazon-orange);
      color: var(--amazon-blue);
      border: none;
      padding: 6px 14px;
      border-radius: var(--border-radius);
      font-weight: 400;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      text-decoration: none;
      font-size: 14px;
    }
    
    .back-btn:hover {
      background: var(--amazon-light-orange);
      color: var(--amazon-blue);
    }
    
    /* Card Styles */
    .card {
      border-radius: var(--border-radius);
      border: 1px solid var(--amazon-border);
      box-shadow: none;
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .card-header {
      background: var(--amazon-light-gray);
      color: var(--amazon-text);
      padding: 14px 20px;
      font-weight: 500;
      border-bottom: 1px solid var(--amazon-border);
      font-size: 16px;
      display: flex;
      align-items: center;
    }
    
    .card-header i {
      margin-right: 8px;
      color: var(--amazon-orange);
    }
    
    .card-body {
      padding: 24px;
    }
    
    /* Product Summary */
    .product-details {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
    }
    
    .summary-img {
      width: 140px;
      height: 140px;
      object-fit: contain;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      padding: 8px;
      background: white;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .summary-img:hover {
      border-color: var(--amazon-orange);
    }
    
    .summary-img.selected {
      border: 2px solid var(--amazon-orange);
    }
    
    .product-info h5 {
      font-weight: 400;
      margin-bottom: 8px;
      color: var(--amazon-text);
      font-size: 20px;
      line-height: 1.3;
    }
    
    .product-info .price {
      font-size: 24px;
      font-weight: 400;
      color: var(--amazon-text);
    }
    
    .product-info .original-price {
      text-decoration: line-through;
      color: var(--amazon-light-text);
      font-size: 16px;
      margin-left: 10px;
    }
    
    .discount-badge {
      background: #CC0C39;
      color: white;
      font-size: 13px;
      padding: 2px 6px;
      border-radius: var(--border-radius);
      font-weight: 700;
      margin-left: 10px;
      vertical-align: super;
    }
    
    /* Image Selector */
    .image-selector {
      display: flex;
      gap: 10px;
      margin-top: 15px;
      flex-wrap: wrap;
    }
    
    .image-selector .summary-img {
      width: 70px;
      height: 70px;
      padding: 4px;
    }
    
    /* Quantity Control */
    .quantity-control {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    
    .quantity-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid var(--amazon-border);
      background: white;
      color: var(--amazon-text);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .quantity-btn:hover {
      background: var(--amazon-light-gray);
      border-color: var(--amazon-orange);
    }
    
    .quantity-input {
      width: 70px;
      text-align: center;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      font-weight: 400;
      height: 36px;
    }
    
    /* Delivery Options */
    .delivery-options {
      display: flex;
      gap: 15px;
      margin-top: 15px;
      flex-wrap: wrap;
    }
    
    .delivery-option {
      flex: 1;
      min-width: 140px;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      padding: 16px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background-color: white;
    }
    
    .delivery-option:hover {
      border-color: var(--amazon-orange);
    }
    
    .delivery-option.selected {
      border-color: var(--amazon-orange);
      box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
    }
    
    .delivery-option h6 {
      font-weight: 500;
      margin-bottom: 5px;
      color: var(--amazon-text);
      font-size: 16px;
    }
    
    .delivery-option p {
      margin-bottom: 5px;
      font-size: 13px;
      color: var(--amazon-light-text);
    }
    
    .delivery-option .price {
      color: var(--amazon-text);
      font-weight: 500;
      font-size: 16px;
    }
    
    /* Payment Methods */
    .payment-methods {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 15px;
    }
    
    .payment-method {
      flex: 1;
      min-width: 130px;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      padding: 16px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      background-color: white;
    }
    
    .payment-method:hover {
      border-color: var(--amazon-orange);
    }
    
    .payment-method.selected {
      border-color: var(--amazon-orange);
      box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
    }
    
    .payment-method i {
      font-size: 28px;
      margin-bottom: 8px;
      color: var(--amazon-orange);
    }
    
    .payment-method div {
      font-weight: 400;
      font-size: 14px;
      color: var(--amazon-text);
    }
    
    /* Total Section */
    .total-section {
      background: var(--amazon-light-gray);
      border-radius: var(--border-radius);
      padding: 20px;
      margin-top: 20px;
      border: 1px solid var(--amazon-border);
    }
    
    .total-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      font-size: 14px;
      color: var(--amazon-text);
    }
    
    .total-row:last-child {
      margin-bottom: 0;
      padding-top: 15px;
      border-top: 1px solid var(--amazon-border);
      font-size: 20px;
      font-weight: 500;
    }
    
    /* Form Styles */
    .form-label {
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 6px;
      color: var(--amazon-text);
    }
    
    .form-control,
    .form-select {
      border-radius: var(--border-radius);
      border: 1px solid var(--amazon-border);
      padding: 10px 12px;
      font-size: 14px;
      transition: var(--transition);
      background-color: white;
      height: 42px;
    }
    
    .form-control:focus,
    .form-select:focus {
      border-color: var(--amazon-orange);
      box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
      background-color: white;
    }
    
    textarea.form-control {
      height: auto;
    }
    
    /* Buttons */
    .btn {
      border-radius: var(--border-radius);
      font-weight: 400;
      padding: 10px 20px;
      transition: var(--transition);
      font-size: 14px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      height: 42px;
    }
    
    .btn-success {
      background: var(--amazon-orange);
      border: none;
      color: var(--amazon-blue);
      border-radius: 20px;
    }
    
    .btn-success:hover {
      background: var(--amazon-light-orange);
    }
    
    .btn-outline-secondary {
      border: 1px solid var(--amazon-border);
      color: var(--amazon-text);
      background-color: white;
      border-radius: 20px;
    }
    
    .btn-outline-secondary:hover {
      background-color: var(--amazon-light-gray);
    }
    
    /* Exclusive Product Styles */
    .exclusive-product {
      background: var(--amazon-light-gray);
      border-radius: var(--border-radius);
      padding: 16px;
      margin-bottom: 20px;
      border: 1px solid var(--amazon-border);
    }
    
    .exclusive-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .exclusive-badge {
      background: var(--amazon-orange);
      color: var(--amazon-blue);
      font-size: 13px;
      padding: 3px 8px;
      border-radius: var(--border-radius);
      font-weight: 700;
      text-transform: uppercase;
    }
    
    .stock-status {
      display: flex;
      align-items: center;
      gap: 5px;
      margin-top: 10px;
      font-size: 14px;
    }
    
    .stock-status i {
      font-size: 16px;
    }
    
    .stock-status.in-stock {
      color: #007600;
    }
    
    .stock-status.out-of-stock {
      color: #B12704;
    }
    
    /* Color and Size Selectors */
    .color-size-selector {
      display: flex;
      gap: 20px;
      margin-top: 15px;
    }
    
    .color-selector, .size-selector {
      flex: 1;
    }
    
    .color-options, .size-options {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    
    .color-option, .size-option {
      padding: 8px 14px;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      background: white;
      cursor: pointer;
      transition: var(--transition);
      font-size: 14px;
      font-weight: 400;
    }
    
    .color-option:hover, .size-option:hover {
      border-color: var(--amazon-orange);
    }
    
    .color-option.selected, .size-option.selected {
      border-color: var(--amazon-orange);
      box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
    }
    
    /* Form Check */
    .form-check {
      margin-bottom: 15px;
    }
    
    .form-check-input:checked {
      background-color: var(--amazon-orange);
      border-color: var(--amazon-orange);
    }
    
    .form-check-input:focus {
      box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
    }
    
    .form-check-label a {
      color: var(--amazon-orange);
      text-decoration: none;
    }
    
    .form-check-label a:hover {
      text-decoration: underline;
    }
    
    .section-title {
      font-weight: 500;
      margin-bottom: 10px;
      color: var(--amazon-text);
      font-size: 16px;
      display: flex;
      align-items: center;
    }
    
    .section-title i {
      margin-right: 8px;
      color: var(--amazon-orange);
    }
    
    /* Responsive Styles */
    @media (max-width: 991px) {
      .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
      }
      
      .product-details {
        flex-direction: column;
        text-align: center;
      }
      
      .image-selector {
        justify-content: center;
      }
    }
    
    @media (max-width: 767px) {
      .checkout-container {
        padding: 0 10px;
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
        min-width: calc(50% - 5px);
      }
      
      .color-size-selector {
        flex-direction: column;
      }
    }
    
    @media (max-width: 576px) {
      .page-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
      }
      
      .payment-method {
        min-width: 100%;
      }
      
      .card-body {
        padding: 16px;
      }
    }
  </style>
</head>
<body>
  <div class="page-header">
    <div class="checkout-container">
      <h1 class="page-title">Checkout</h1>
      <a href="javascript:history.back()" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Shopping
      </a>
    </div>
  </div>
  
  <div class="checkout-container">
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
              <div class="section-title mt-3">
                <i class="fas fa-sort-numeric-up"></i> Quantity
              </div>
              <div class="quantity-control">
                <button type="button" class="quantity-btn" id="decreaseQty"><i class="fas fa-minus"></i></button>
                <input type="number" class="form-control quantity-input" name="quantity" id="quantity" value="1" min="1" required>
                <button type="button" class="quantity-btn" id="increaseQty"><i class="fas fa-plus"></i></button>
              </div>
              
              <!-- Delivery Options -->
              <div class="section-title mt-3">
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
                <div class="col-12 mt-3">
                  <button type="submit" class="btn btn-success w-100">
                    <i class="fas fa-check-circle"></i> Place Order
                  </button>
                  <a href="javascript:history.back()" class="btn btn-outline-secondary w-100 mt-2">
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
        });
      });
      
      // Handle size selection
      sizeOptions.forEach(opt => {
        opt.addEventListener("click", function() {
          sizeOptions.forEach(o => o.classList.remove("selected"));
          this.classList.add("selected");
          sizeIdInput.value = this.dataset.id;
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
      });
    }
    
    // Initialize total
    updateTotal();
  </script>
</body>
</html>