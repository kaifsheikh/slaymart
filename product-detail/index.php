<?php
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
      --primary-color: #5e72e4;
      --secondary-color: #825ee4;
      --dark-color: #32325d;
      --light-color: #f7fafc;
      --success-color: #2dce89;
      --info-color: #11cdef;
      --warning-color: #fb6340;
      --danger-color: #f5365c;
      --gray-color: #8898aa;
      --light-gray: #f4f5f7;
      --border-radius: 12px;
      --box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fe;
      color: var(--dark-color);
      line-height: 1.6;
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
      box-shadow: 0 18px 40px rgba(50, 50, 93, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    
    .product-gallery {
      padding: 30px;
      background: var(--light-color);
      position: relative;
    }
    
    .main-image-container {
      position: relative;
      margin-bottom: 20px;
      overflow: hidden;
      border-radius: var(--border-radius);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      background: white;
      padding: 15px;
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
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .thumbnail-container {
      display: flex;
      gap: 15px;
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
      min-width: 90px;
      height: 90px;
      border-radius: 10px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
      transition: var(--transition);
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .thumbnail:hover {
      border-color: var(--primary-color);
      transform: translateY(-3px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
    
    .thumbnail.active {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.2);
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
      color: var(--primary-color);
      font-weight: 600;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 10px;
      display: inline-block;
      padding: 5px 12px;
      background: rgba(94, 114, 228, 0.1);
      border-radius: 20px;
    }
    
    .product-title {
      font-size: 2.2rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 20px;
      line-height: 1.2;
    }
    
    .product-description {
      color: var(--gray-color);
      margin-bottom: 30px;
      line-height: 1.8;
      font-size: 1rem;
    }
    
    .price-container {
      margin-bottom: 35px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .current-price {
      font-size: 2rem;
      font-weight: 700;
      color: var(--primary-color);
    }
    
    .original-price {
      font-size: 1.3rem;
      color: var(--gray-color);
      text-decoration: line-through;
      margin-left: 15px;
    }
    
    .discount-badge {
      background: var(--danger-color);
      color: white;
      font-size: 0.8rem;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: 600;
      margin-left: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .product-actions {
      display: flex;
      gap: 15px;
      margin-bottom: 35px;
    }
    
    .btn {
      padding: 14px 28px;
      border-radius: 8px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      font-size: 1rem;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
      color: white;
    }
    
    .btn-outline-primary {
      background: white;
      color: var(--primary-color);
      border: 1px solid var(--primary-color);
      box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
    }
    
    .btn-outline-primary:hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-3px);
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    .product-details {
      background: var(--light-color);
      border-radius: var(--border-radius);
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05);
    }
    
    .detail-item {
      display: flex;
      margin-bottom: 18px;
      padding-bottom: 18px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
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
      margin-top: 40px;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
    }
    
    .nav-tabs {
      border-bottom: none;
      background: var(--light-color);
      padding: 0 20px;
    }
    
    .nav-tabs .nav-link {
      border: none;
      color: var(--gray-color);
      font-weight: 600;
      padding: 15px 25px;
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
    }
    
    .nav-tabs .nav-link.active::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
      border-radius: 3px 3px 0 0;
    }
    
    .tab-content {
      padding: 30px;
    }
    
    .tab-pane p {
      color: var(--gray-color);
      line-height: 1.8;
      margin-bottom: 0;
    }
    
    .review-item {
      padding: 20px;
      border-radius: var(--border-radius);
      background: var(--light-color);
      margin-bottom: 20px;
      box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05);
      transition: var(--transition);
    }
    
    .review-item:hover {
      transform: translateY(-3px);
      box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
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
      color: #ffc107;
      margin-bottom: 10px;
      font-size: 1.1rem;
    }
    
    .review-text {
      color: var(--gray-color);
      line-height: 1.7;
    }
    
    .related-products {
      margin-top: 50px;
    }
    
    .section-title {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--dark-color);
      margin-bottom: 30px;
      position: relative;
      padding-left: 20px;
      display: inline-block;
    }
    
    .section-title::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 6px;
      height: 70%;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border-radius: 3px;
    }
    
    .product-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
      transition: var(--transition);
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    .product-card-img {
      height: 220px;
      overflow: hidden;
      position: relative;
    }
    
    .product-card-img::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom, rgba(0,0,0,0) 70%, rgba(0,0,0,0.3) 100%);
      z-index: 1;
    }
    
    .product-card-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }
    
    .product-card:hover .product-card-img img {
      transform: scale(1.08);
    }
    
    .product-card-body {
      padding: 20px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }
    
    .product-card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--dark-color);
      margin-bottom: 12px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .product-card-price {
      font-weight: 700;
      color: var(--primary-color);
      font-size: 1.2rem;
      margin-top: auto;
    }
    
    .product-card-original-price {
      font-size: 0.9rem;
      color: var(--gray-color);
      text-decoration: line-through;
      margin-left: 8px;
    }
    
    .no-reviews {
      padding: 30px;
      text-align: center;
      color: var(--gray-color);
      background: var(--light-color);
      border-radius: var(--border-radius);
    }
    
    .no-reviews i {
      font-size: 3rem;
      color: var(--primary-color);
      margin-bottom: 15px;
      opacity: 0.7;
    }
    
    /* Responsive Design */
    @media (max-width: 991px) {
      .product-gallery {
        margin-bottom: 30px;
      }
      
      .product-info {
        padding: 20px;
      }
      
      .product-title {
        font-size: 1.8rem;
      }
      
      .main-image {
        height: 350px;
      }
    }
    
    @media (max-width: 767px) {
      .product-detail-container {
        padding: 10px;
      }
      
      .product-gallery,
      .product-info {
        padding: 20px;
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
      .product-title {
        font-size: 1.5rem;
      }
      
      .current-price {
        font-size: 1.6rem;
      }
      
      .original-price {
        font-size: 1rem;
      }
      
      .discount-badge {
        font-size: 0.7rem;
        padding: 4px 8px;
      }
      
      .section-title {
        font-size: 1.5rem;
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
                <div class="detail-value">In Stock</div>
              </div>
              <div class="detail-item">
                <div class="detail-label">Delivery:</div>
                <div class="detail-value">3-5 working days</div>
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