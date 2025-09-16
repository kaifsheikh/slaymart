<?php
// Start session at the beginning
// session_start();

include "../config/db.php";
// Validate Product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid Product ID");
}
$id = intval($_GET['id']);
// Fetch product details
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
  die("Product not found");
}
$product = $result->fetch_assoc();
// Sanitize
$productName        = htmlspecialchars($product['name']);
$productDescription = nl2br(htmlspecialchars($product['description']));
$productCategory    = htmlspecialchars($product['category']);
// Price Calculation
$originalPrice   = floatval($product['price']);
$stock   = htmlspecialchars($product['stock_status']);
$productDiscount = floatval($product['discount']);
$productSalePrice = $originalPrice;
if ($productDiscount > 0) {
  $productSalePrice = $originalPrice - ($originalPrice * $productDiscount / 100);
}
// Format prices
$productPriceFormatted     = number_format($originalPrice);
$productSalePriceFormatted = number_format($productSalePrice);
// ✅ Fetch product images from product_images table
$productImages = [];
$imgStmt = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC");
$imgStmt->bind_param("i", $id);
$imgStmt->execute();
$imgRes = $imgStmt->get_result();
while ($imgRow = $imgRes->fetch_assoc()) {
  $productImages[] = htmlspecialchars($imgRow['image']);
}
if (empty($productImages)) {
  $productImages[] = "placeholder.png"; // fallback
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $productName ?> - Slaymart</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #FF9900;
      --secondary-color: #232F3E;
      --accent-color: #37475A;
      --dark-color: #131921;
      --light-color: #F7F7F7;
      --success-color: #2dce89;
      --info-color: #11cdef;
      --warning-color: #fb6340;
      --danger-color: #f5365c;
      --gray-color: #565959;
      --light-gray: #f4f5f7;
      --border-radius: 8px;
      --box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #EAEDED;
      color: var(--dark-color);
      line-height: 1.6;
    }
    
    /* Header Styles */
    .header {
      background: var(--dark-color);
      padding: 15px 0;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .header-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .logo {
      display: flex;
      align-items: center;
      text-decoration: none;
    }
    
    .logo img {
      height: 40px;
      margin-right: 10px;
    }
    
    .logo-text {
      color: white;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: -1px;
    }
    
    .logo-text span {
      color: var(--primary-color);
    }
    
    .user-area {
      display: flex;
      align-items: center;
      gap: 20px;
    }
    
    .user-greeting {
      color: white;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .user-greeting i {
      color: var(--primary-color);
    }
    
    .auth-links {
      display: flex;
      gap: 15px;
    }
    
    .auth-links a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 8px 15px;
      border-radius: 4px;
      transition: var(--transition);
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .auth-links a:hover {
      background: rgba(255,255,255,0.1);
    }
    
    .auth-links .register {
      background: var(--primary-color);
      color: var(--dark-color);
    }
    
    .auth-links .register:hover {
      background: #e88b00;
    }
    
    .product-detail-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    .breadcrumb {
      background: none;
      padding: 15px 0;
      margin-bottom: 20px;
      font-size: 0.9rem;
    }
    
    .breadcrumb-item+.breadcrumb-item::before {
      content: ">";
      color: var(--gray-color);
    }
    
    .breadcrumb-item a {
      color: var(--primary-color);
      text-decoration: none;
      font-weight: 500;
    }
    
    .breadcrumb-item a:hover {
      text-decoration: underline;
    }
    
    .breadcrumb-item.active {
      color: var(--dark-color);
      font-weight: 600;
    }
    
    .product-detail-card {
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      margin-bottom: 30px;
      transition: var(--transition);
    }
    
    .product-detail-card:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .product-gallery {
      padding: 30px;
      background: white;
      position: relative;
    }
    
    .main-image-container {
      position: relative;
      margin-bottom: 20px;
      overflow: hidden;
      border-radius: var(--border-radius);
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
      background: white;
      padding: 15px;
      border: 1px solid #DDD;
    }
    
    .main-image {
      width: 100%;
      height: 450px;
      object-fit: contain;
      transition: var(--transition);
      cursor: zoom-in;
    }
    
    .main-image:hover {
      transform: scale(1.03);
    }
    
    .zoom-result {
      position: absolute;
      top: 0;
      left: 105%;
      width: 400px;
      height: 400px;
      border-radius: var(--border-radius);
      border: 1px solid #ddd;
      background-repeat: no-repeat;
      background-size: 800px auto;
      display: none;
      z-index: 100;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .thumbnail-container {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding: 10px 0;
      scrollbar-width: thin;
      scrollbar-color: var(--primary-color) var(--light-gray);
    }
    
    .thumbnail-container::-webkit-scrollbar {
      height: 6px;
    }
    
    .thumbnail-container::-webkit-scrollbar-track {
      background: var(--light-gray);
      border-radius: 10px;
    }
    
    .thumbnail-container::-webkit-scrollbar-thumb {
      background-color: var(--primary-color);
      border-radius: 10px;
    }
    
    .thumbnail {
      min-width: 80px;
      height: 80px;
      border-radius: 4px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
      transition: var(--transition);
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    
    .thumbnail:hover {
      border-color: var(--primary-color);
      transform: translateY(-2px);
      box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    
    .thumbnail.active {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 2px rgba(255,153,0,0.3);
    }
    
    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .product-info {
      padding: 30px;
    }
    
    .product-category {
      color: var(--gray-color);
      font-weight: 500;
      font-size: 0.9rem;
      margin-bottom: 10px;
    }
    
    .product-title {
      font-size: 1.8rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 15px;
      line-height: 1.2;
    }
    
    .product-description {
      color: var(--gray-color);
      margin-bottom: 20px;
      line-height: 1.6;
      font-size: 0.95rem;
    }
    
    .price-container {
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      background: #F7F7F7;
      padding: 15px;
      border-radius: var(--border-radius);
    }
    
    .current-price {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark-color);
    }
    
    .original-price {
      font-size: 1.1rem;
      color: var(--gray-color);
      text-decoration: line-through;
      margin-left: 15px;
    }
    
    .discount-badge {
      background: var(--danger-color);
      color: white;
      font-size: 0.8rem;
      padding: 4px 8px;
      border-radius: 4px;
      font-weight: 600;
      margin-left: 15px;
    }
    
    .product-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }
    
    .btn {
      padding: 12px 20px;
      border-radius: var(--border-radius);
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      font-size: 0.95rem;
    }
    
    .btn-primary {
      background: var(--primary-color);
      color: var(--dark-color);
      box-shadow: 0 2px 3px rgba(0,0,0,0.1);
    }
    
    .btn-primary:hover {
      background: #e88b00;
      transform: translateY(-1px);
      box-shadow: 0 3px 5px rgba(0,0,0,0.15);
      color: var(--dark-color);
    }
    
    .btn-outline-primary {
      background: white;
      color: var(--primary-color);
      border: 1px solid var(--primary-color);
    }
    
    .btn-outline-primary:hover {
      background: var(--light-color);
      color: var(--dark-color);
      transform: translateY(-1px);
      box-shadow: 0 2px 3px rgba(0,0,0,0.1);
    }
    
    .product-details {
      background: #F7F7F7;
      border-radius: var(--border-radius);
      padding: 20px;
      margin-bottom: 25px;
      border: 1px solid #DDD;
    }
    
    .detail-item {
      display: flex;
      margin-bottom: 15px;
      padding-bottom: 15px;
      border-bottom: 1px solid #DDD;
    }
    
    .detail-item:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }
    
    .detail-label {
      font-weight: 600;
      color: var(--dark-color);
      width: 140px;
      font-size: 0.95rem;
    }
    
    .detail-value {
      color: var(--gray-color);
      font-size: 0.95rem;
    }
    
    .product-tabs {
      margin-top: 30px;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
    }
    
    .nav-tabs {
      border-bottom: 1px solid #DDD;
      background: var(--light-color);
      padding: 0 15px;
    }
    
    .nav-tabs .nav-link {
      border: none;
      color: var(--gray-color);
      font-weight: 600;
      padding: 15px 20px;
      border-radius: 0;
      position: relative;
      transition: var(--transition);
    }
    
    .nav-tabs .nav-link:hover {
      color: var(--primary-color);
      background: transparent;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--primary-color);
      background: transparent;
      border-bottom: 3px solid var(--primary-color);
    }
    
    .tab-content {
      padding: 25px;
    }
    
    .tab-pane p {
      color: var(--gray-color);
      line-height: 1.7;
      margin-bottom: 0;
    }
    
    .review-item {
      padding: 20px;
      border-radius: var(--border-radius);
      background: #F7F7F7;
      margin-bottom: 20px;
      border: 1px solid #DDD;
      transition: var(--transition);
    }
    
    .review-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    
    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .reviewer-name {
      font-weight: 600;
      color: var(--dark-color);
    }
    
    .review-date {
      color: var(--gray-color);
      font-size: 0.85rem;
    }
    
    .review-rating {
      color: #FF9900;
      margin-bottom: 10px;
      font-size: 1.1rem;
    }
    
    .review-text {
      color: var(--gray-color);
      line-height: 1.6;
    }
    
    .related-products {
      margin-top: 40px;
    }
    
    .section-title {
      font-size: 1.6rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 25px;
      position: relative;
      padding-left: 15px;
      display: inline-block;
    }
    
    .section-title::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 5px;
      height: 70%;
      background: var(--primary-color);
      border-radius: 3px;
    }
    
    .product-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      transition: var(--transition);
      height: 100%;
      display: flex;
      flex-direction: column;
      border: 1px solid #DDD;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
    
    .product-card-img {
      height: 200px;
      overflow: hidden;
      position: relative;
      background: white;
    }
    
    .product-card-img img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      transition: var(--transition);
    }
    
    .product-card:hover .product-card-img img {
      transform: scale(1.05);
    }
    
    .product-card-body {
      padding: 15px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .product-card-title {
      font-size: 1rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 10px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .product-card-price {
      font-weight: 700;
      color: var(--dark-color);
      font-size: 1.1rem;
      margin-top: auto;
    }
    
    .product-card-original-price {
      font-size: 0.85rem;
      color: var(--gray-color);
      text-decoration: line-through;
      margin-left: 8px;
    }
    
    .no-reviews {
      padding: 30px;
      text-align: center;
      color: var(--gray-color);
      background: #F7F7F7;
      border-radius: var(--border-radius);
      border: 1px solid #DDD;
    }
    
    .no-reviews i {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 15px;
      opacity: 0.7;
    }
    
    .delivery-info {
      background: #F7F7F7;
      border-radius: var(--border-radius);
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid #DDD;
    }
    
    .delivery-info h4 {
      font-size: 1rem;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--dark-color);
    }
    
    .delivery-info p {
      margin-bottom: 5px;
      font-size: 0.9rem;
      color: var(--gray-color);
    }
    
    .delivery-info i {
      color: var(--primary-color);
      margin-right: 8px;
    }
    
    /* Responsive Design */
    @media (max-width: 991px) {
      .header-container {
        padding: 0 15px;
      }
      
      .logo-text {
        font-size: 1.5rem;
      }
      
      .user-greeting {
        font-size: 0.9rem;
      }
      
      .auth-links {
        gap: 10px;
      }
      
      .auth-links a {
        padding: 6px 12px;
        font-size: 0.9rem;
      }
      
      .product-gallery {
        margin-bottom: 30px;
      }
      
      .product-info {
        padding: 20px;
      }
      
      .product-title {
        font-size: 1.6rem;
      }
      
      .main-image {
        height: 350px;
      }
    }
    
    @media (max-width: 767px) {
      .header-container {
        flex-direction: column;
        gap: 15px;
      }
      
      .user-area {
        width: 100%;
        justify-content: space-between;
      }
      
      .product-detail-container {
        padding: 10px;
      }
      
      .product-gallery,
      .product-info {
        padding: 15px;
      }
      
      .main-image {
        height: 300px;
      }
      
      .zoom-result {
        width: 300px;
        height: 300px;
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
      
      .nav-tabs .nav-link {
        padding: 12px 15px;
        font-size: 0.9rem;
      }
      
      .tab-content {
        padding: 20px;
      }
    }
    
    @media (max-width: 575px) {
      .logo-text {
        font-size: 1.3rem;
      }
      
      .user-greeting {
        display: none;
      }
      
      .auth-links {
        width: 100%;
        justify-content: center;
      }
      
      .product-title {
        font-size: 1.4rem;
      }
      
      .current-price {
        font-size: 1.5rem;
      }
      
      .original-price {
        font-size: 1rem;
      }
      
      .discount-badge {
        font-size: 0.7rem;
        padding: 3px 6px;
      }
      
      .section-title {
        font-size: 1.4rem;
      }
      
      .thumbnail {
        min-width: 70px;
        height: 70px;
      }
    }
    
    /* Animation for page load */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .product-detail-card,
    .product-tabs,
    .related-products {
      animation: fadeIn 0.6s ease forwards;
    }
    
    .product-tabs {
      animation-delay: 0.2s;
    }
    
    .related-products {
      animation-delay: 0.4s;
    }
  </style>
  <link rel="shortcut icon" href="../images/logo/favicon.png" type="image/x-icon">
</head>
<body>
  <!-- Header Section -->
  <header class="header">
    <div class="header-container">
      <a href="../index.php" class="logo">
        <!-- <img src="../images/logo/logo.png" alt="Slaymart Logo"> -->
        <div class="logo-text">Slay<span>mart</span></div>
      </a>
      
      <div class="user-area">
        <?php if (isset($_SESSION['user_name'])): ?>
          <div class="user-greeting">
            <i class="fas fa-user-circle"></i>
            Hello, <?= htmlspecialchars($_SESSION['user_name']) ?>
          </div>
          <div class="auth-links">
            <a href="../users/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
          </div>
        <?php else: ?>
          <div class="auth-links">
            <a href="../users/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="../users/register.php" class="register"><i class="fas fa-user-plus"></i> Register</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </header>
  
  <div class="product-detail-container">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="../index.php?category=<?= urlencode($productCategory) ?>"><?= $productCategory ?></a></li>
        <li class="breadcrumb-item active"><?= $productName ?></li>
      </ol>
    </nav>
    
    <!-- Product Detail -->
    <div class="product-detail-card">
      <div class="row g-0">
        <!-- Product Gallery -->
        <div class="col-lg-6">
          <div class="product-gallery">
            <div class="main-image-container">
              <img src="../images/uploads/<?= $productImages[0] ?>" alt="<?= $productName ?>" class="main-image" id="mainImage">
              <div class="zoom-result" id="zoomResult"></div>
            </div>
            <!-- Thumbnails -->
            <div class="thumbnail-container">
              <?php foreach ($productImages as $i => $img) : ?>
                <div class="thumbnail <?= $i == 0 ? 'active' : '' ?>" data-image="../images/uploads/<?= $img ?>">
                  <img src="../images/uploads/<?= $img ?>" alt="<?= $productName ?> thumbnail <?= $i + 1 ?>">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-6">
          <div class="product-info">
            <p class="product-category"><?= $productCategory ?></p>
            <h1 class="product-title"><?= $productName ?></h1>
            <p class="product-description"><?= $productDescription ?></p>
            
            <!-- Price Info -->
            <div class="price-container">
              <?php if ($productDiscount > 0): ?>
                <span class="current-price">PKR <?= $productSalePriceFormatted ?></span>
                <span class="original-price">PKR <?= $productPriceFormatted ?></span>
                <span class="discount-badge"><?= $productDiscount ?>% OFF</span>
              <?php else: ?>
                <span class="current-price">PKR <?= $productPriceFormatted ?></span>
              <?php endif; ?>
            </div>
            
            <!-- Delivery Info -->
            <div class="delivery-info">
              <h4><i class="fas fa-truck"></i> Delivery Information</h4>
              <p><i class="fas fa-check-circle"></i> Free delivery on orders above PKR 2000</p>
              <p><i class="fas fa-clock"></i> Standard delivery: 3-5 working days</p>
              <p><i class="fas fa-undo"></i> Return Policy will coming soon</p>
            </div>
            
            <!-- Product Actions -->
            <div class="product-actions">
              <a href="../checkout/buy_now.php?id=<?= $product['id'] ?>" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Buy Now
              </a>
              <a href="../feedback/review_feedback.php?product_id=<?= $id; ?>" class="btn btn-outline-primary">
                <i class="fas fa-comment"></i> Feedback
              </a>
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
              <h3 class="section-title">Product Details</h3>
              <div class="detail-item">
                <div class="detail-label">Category:</div>
                <div class="detail-value"><?= $productCategory ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Availability:</div>
                <!-- <div class="detail-value">In Stock</div> -->
                <div class="detail-value"> Stock <?= $stock ?></div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Brand:</div>
                <div class="detail-value">None</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Product Tabs -->
    <div class="product-tabs">
      <ul class="nav nav-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">Description</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">Specifications</button>
        </li>
      </ul>
      <div class="tab-content" id="productTabsContent">
        <div class="tab-pane fade show active" id="description" role="tabpanel">
          <p><?= $productDescription ?></p>
        </div>
        
        <?php
        // ✅ Fetch reviews for this product
        $reviewsStmt = $conn->prepare("SELECT r.*, u.name AS user_name FROM reviews AS r JOIN users AS u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
        $reviewsStmt->bind_param("i", $id);
        $reviewsStmt->execute();
        $reviewsResult = $reviewsStmt->get_result();
        ?>
        
        <div class="tab-pane fade" id="reviews" role="tabpanel">
          <?php if ($reviewsResult && $reviewsResult->num_rows > 0): ?>
            <?php while ($review = $reviewsResult->fetch_assoc()): ?>
              <div class="review-item">
                <div class="review-header">
                  <strong class="reviewer-name"><?= htmlspecialchars($review['user_name']) ?></strong>
                  <span class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></span>
                </div>
                <div class="review-rating">
                  <?= str_repeat('★', intval($review['rating'])) ?>
                  <?= str_repeat('☆', 5 - intval($review['rating'])) ?>
                </div>
                <p class="review-text"><?= nl2br(htmlspecialchars($review['feedback'])) ?></p>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <div class="no-reviews">
              <i class="fas fa-comments"></i>
              <p>No reviews yet. Be the first to review this product!</p>
            </div>
          <?php endif; ?>
        </div>
        
        <div class="tab-pane fade" id="specs" role="tabpanel">
          <div class="product-details">
            <div class="detail-item">
              <div class="detail-label">Material:</div>
              <div class="detail-value">High Quality</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Dimensions:</div>
              <div class="detail-value">Standard Size</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Warranty:</div>
              <div class="detail-value">1 Year Manufacturer Warranty</div>
            </div>
            <div class="detail-item">
              <div class="detail-label">Country of Origin:</div>
              <div class="detail-value">Pakistan</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Related Products -->
    <div class="related-products">
      <h2 class="section-title">Related Products</h2>
      <div class="row g-4">
        <?php
        // ⚡ Use raw category from DB (not htmlspecialchars one)
        $rawCategory = $product['category'];
        $relatedStmt = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
        $relatedStmt->bind_param("si", $rawCategory, $id);
        $relatedStmt->execute();
        $relatedResult = $relatedStmt->get_result();
        if ($relatedResult && $relatedResult->num_rows > 0) {
          while ($relatedProduct = $relatedResult->fetch_assoc()) {
            $relatedId = $relatedProduct['id'];
            $relatedName = htmlspecialchars($relatedProduct['name']);
            $relatedPrice = number_format($relatedProduct['price']);
            $relatedDiscount = floatval($relatedProduct['discount']);
            $relatedSalePrice = $relatedPrice;
            if ($relatedDiscount > 0) {
              $originalPrice = floatval($relatedProduct['price']);
              $relatedSalePrice = number_format($originalPrice - ($originalPrice * $relatedDiscount / 100));
            }
            // ✅ Get first image from product_images table
            $relatedImgRes = mysqli_query($conn, "SELECT image FROM product_images WHERE product_id = {$relatedId} ORDER BY id ASC LIMIT 1");
            $relatedImgRow = mysqli_fetch_assoc($relatedImgRes);
            $relatedImage = $relatedImgRow['image'] ?? 'placeholder.png';
        ?>
            <div class="col-6 col-md-3">
              <a href="index.php?id=<?= $relatedId ?>" class="text-decoration-none">
                <div class="product-card">
                  <div class="product-card-img">
                    <img src="../images/uploads/<?= $relatedImage ?>" alt="<?= $relatedName ?>">
                  </div>
                  <div class="product-card-body">
                    <h3 class="product-card-title"><?= $relatedName ?></h3>
                    <div class="product-card-price">
                      <?php if ($relatedDiscount > 0): ?>
                        PKR <?= $relatedSalePrice ?>
                        <span class="product-card-original-price">PKR <?= $relatedPrice ?></span>
                      <?php else: ?>
                        PKR <?= $relatedPrice ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </a>
            </div>
        <?php
          }
        } else {
          echo '<div class="col-12"><div class="no-reviews"><i class="fas fa-box-open"></i><p>No related products found.</p></div></div>';
        }
        ?>
      </div>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Image zoom functionality
    const mainImage = document.getElementById("mainImage");
    const zoomResult = document.getElementById("zoomResult");
    if (mainImage) {
      mainImage.addEventListener("mousemove", function(e) {
        const rect = mainImage.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const xPercent = (x / rect.width) * 100;
        const yPercent = (y / rect.height) * 100;
        zoomResult.style.backgroundImage = `url('${mainImage.src}')`;
        zoomResult.style.backgroundPosition = `${xPercent}% ${yPercent}%`;
        zoomResult.style.display = "block";
      });
      
      mainImage.addEventListener("mouseleave", function() {
        zoomResult.style.display = "none";
      });
    }
    
    // Thumbnail functionality
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', function() {
        thumbnails.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        mainImage.src = this.getAttribute('data-image');
      });
    });
    
    // Smooth scroll animation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  </script>
</body>
</html>