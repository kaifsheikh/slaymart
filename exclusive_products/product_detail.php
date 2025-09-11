<?php
include "../config/db.php";
if (!isset($_GET['id'])) {
    die("Product not found!");
}
$id = intval($_GET['id']);

// --- Product Data ---
$query = "SELECT p.*, ROUND(AVG(r.rating),1) AS avg_rating
          FROM products p
          LEFT JOIN reviews r ON p.id = r.product_id
          WHERE p.id = $id AND p.type = 'exclusive'
          GROUP BY p.id
          LIMIT 1";
$result = mysqli_query($conn, $query);
$product = mysqli_fetch_assoc($result);
if (!$product) {
    die("Product not found or not exclusive!");
}

// --- Product Images ---
$img_sql = "SELECT image FROM product_images WHERE product_id = $id";
$img_res = mysqli_query($conn, $img_sql);
$images = [];
while ($img = mysqli_fetch_assoc($img_res)) {
    $images[] = $img['image'];
}

// --- Colors ---
$color_sql = "SELECT c.name FROM product_colors pc 
              JOIN colors c ON pc.color_id = c.id 
              WHERE pc.product_id = $id";
$color_res = mysqli_query($conn, $color_sql);

// --- Sizes ---
$size_sql = "SELECT s.name FROM product_sizes ps 
             JOIN sizes s ON ps.size_id = s.id 
             WHERE ps.product_id = $id";
$size_res = mysqli_query($conn, $size_sql);

// --- Discount Calculation ---
$final_price = $product['discount'] > 0 
    ? $product['price'] - ($product['price'] * $product['discount'] / 100) 
    : $product['price'];

// --- Reviews ---
$review_sql = "SELECT r.*, u.name AS user_name FROM reviews r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.product_id = $id 
              ORDER BY r.created_at DESC";
$review_res = mysqli_query($conn, $review_sql);

// --- Related Products ---
$related_sql = "SELECT * FROM products 
                WHERE category = '{$product['category']}' 
                AND id != $id 
                AND type = 'exclusive' 
                LIMIT 4";
$related_res = mysqli_query($conn, $related_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> | Premium Product</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #1a1a1a;
      --secondary: #d4af37;
      --accent: #e74c3c;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --border-radius: 8px;
      --transition: all 0.3s ease;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      --shadow-hover: 0 15px 35px rgba(0, 0, 0, 0.15);
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #fff;
      color: var(--dark);
      line-height: 1.6;
      overflow-x: hidden;
    }
    
    h1, h2, h3, h4 {
      font-family: 'Playfair Display', serif;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    .header {
      background: #fff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    
    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 0;
    }
    
    .logo {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary);
      text-decoration: none;
      letter-spacing: 1px;
    }
    
    .logo span {
      color: var(--secondary);
    }
    
    .header-icons {
      display: flex;
      gap: 20px;
    }
    
    .header-icon {
      font-size: 20px;
      color: var(--dark);
      position: relative;
      transition: var(--transition);
    }
    
    .header-icon:hover {
      color: var(--secondary);
    }
    
    .cart-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background: var(--accent);
      color: white;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 10px;
      font-weight: 600;
    }
    
    /* Breadcrumb */
    .breadcrumb {
      padding: 20px 0;
      font-size: 14px;
    }
    
    .breadcrumb-item {
      display: inline-block;
    }
    
    .breadcrumb-item:not(:last-child)::after {
      content: "/";
      margin: 0 10px;
      color: var(--gray);
    }
    
    .breadcrumb-item a {
      color: var(--gray);
      text-decoration: none;
      transition: var(--transition);
    }
    
    .breadcrumb-item a:hover {
      color: var(--secondary);
    }
    
    .breadcrumb-item.active {
      color: var(--primary);
      font-weight: 500;
    }
    
    /* Product Section */
    .product-section {
      padding: 40px 0;
    }
    
    .product-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 60px;
      align-items: start;
    }
    
    /* Product Gallery */
    .product-gallery {
      position: sticky;
      top: 100px;
    }
    
    .main-image {
      width: 100%;
      height: 500px;
      object-fit: cover;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      margin-bottom: 20px;
      transition: var(--transition);
    }
    
    .main-image:hover {
      transform: scale(1.02);
    }
    
    .thumbnail-container {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 15px;
    }
    
    .thumbnail {
      width: 100%;
      height: 100px;
      object-fit: cover;
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
      border: 2px solid transparent;
    }
    
    .thumbnail:hover {
      border-color: var(--secondary);
    }
    
    .thumbnail.active {
      border-color: var(--secondary);
    }
    
    /* Product Details */
    .product-details {
      padding: 20px 0;
    }
    
    .product-badge {
      display: inline-block;
      background: var(--secondary);
      color: white;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 15px;
    }
    
    .product-title {
      font-size: 36px;
      font-weight: 700;
      margin-bottom: 15px;
      line-height: 1.2;
      color: var(--primary);
    }
    
    .product-rating {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    
    .stars {
      color: var(--secondary);
    }
    
    .rating-value {
      font-weight: 500;
      color: var(--gray);
    }
    
    .product-price {
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 25px;
    }
    
    .current-price {
      font-size: 28px;
      font-weight: 700;
      color: var(--accent);
    }
    
    .original-price {
      font-size: 18px;
      color: var(--gray);
      text-decoration: line-through;
    }
    
    .discount-badge {
      background: var(--accent);
      color: white;
      padding: 5px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .product-description {
      color: var(--gray);
      margin-bottom: 30px;
      line-height: 1.8;
    }
    
    .product-options {
      margin-bottom: 30px;
    }
    
    .option-title {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .color-options {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
    }
    
    .color-option {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      cursor: pointer;
      border: 3px solid transparent;
      transition: var(--transition);
    }
    
    .color-option:hover {
      transform: scale(1.1);
    }
    
    .color-option.active {
      border-color: var(--primary);
    }
    
    .size-options {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    
    .size-option {
      padding: 8px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      cursor: pointer;
      transition: var(--transition);
      font-weight: 500;
    }
    
    .size-option:hover {
      border-color: var(--primary);
      color: var(--primary);
    }
    
    .size-option.active {
      background: var(--primary);
      color: white;
      border-color: var(--primary);
    }
    
    .product-actions {
      display: flex;
      gap: 15px;
      margin-bottom: 30px;
    }
    
    .btn {
      padding: 15px 30px;
      border-radius: 4px;
      font-weight: 600;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .btn-primary {
      background: var(--primary);
      color: white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .btn-primary:hover {
      background: #333;
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    
    .btn-secondary {
      background: var(--secondary);
      color: white;
      box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
    }
    
    .btn-secondary:hover {
      background: #c9a030;
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(212, 175, 55, 0.4);
    }
    
    .product-meta {
      border-top: 1px solid #eee;
      padding-top: 20px;
      margin-top: 20px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .meta-icon {
      width: 40px;
      height: 40px;
      background: var(--light);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: var(--secondary);
    }
    
    .meta-text {
      font-weight: 500;
    }
    
    /* Tabs Section */
    .tabs-section {
      padding: 60px 0;
      background: var(--light);
    }
    
    .tabs-header {
      display: flex;
      border-bottom: 1px solid #ddd;
      margin-bottom: 30px;
    }
    
    .tab-btn {
      padding: 15px 30px;
      background: none;
      border: none;
      font-weight: 600;
      color: var(--gray);
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      font-size: 16px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .tab-btn:hover {
      color: var(--primary);
    }
    
    .tab-btn.active {
      color: var(--primary);
    }
    
    .tab-btn.active::after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 100%;
      height: 3px;
      background: var(--secondary);
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
      animation: fadeIn 0.5s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .tab-content p {
      color: var(--gray);
      line-height: 1.8;
      margin-bottom: 20px;
    }
    
    /* Reviews */
    .review-item {
      background: white;
      border-radius: var(--border-radius);
      padding: 25px;
      margin-bottom: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    
    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .reviewer-name {
      font-weight: 600;
      color: var(--primary);
    }
    
    .review-date {
      color: var(--gray);
      font-size: 14px;
    }
    
    .review-rating {
      color: var(--secondary);
      margin-bottom: 15px;
    }
    
    .review-text {
      color: var(--gray);
      line-height: 1.7;
    }
    
    .no-reviews {
      text-align: center;
      padding: 40px;
      color: var(--gray);
    }
    
    .no-reviews i {
      font-size: 48px;
      color: var(--secondary);
      margin-bottom: 15px;
    }
    
    /* Related Products */
    .related-section {
      padding: 60px 0;
    }
    
    .section-header {
      text-align: center;
      margin-bottom: 50px;
    }
    
    .section-title {
      font-size: 36px;
      font-weight: 700;
      margin-bottom: 15px;
      color: var(--primary);
    }
    
    .section-subtitle {
      color: var(--gray);
      max-width: 600px;
      margin: 0 auto;
    }
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 30px;
    }
    
    .product-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: var(--transition);
    }
    
    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: var(--shadow-hover);
    }
    
    .product-card-img {
      height: 250px;
      overflow: hidden;
    }
    
    .product-card-img img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }
    
    .product-card:hover .product-card-img img {
      transform: scale(1.1);
    }
    
    .product-card-body {
      padding: 20px;
    }
    
    .product-card-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      color: var(--primary);
    }
    
    .product-card-price {
      font-size: 20px;
      font-weight: 700;
      color: var(--accent);
    }
    
    .product-card-original-price {
      font-size: 16px;
      color: var(--gray);
      text-decoration: line-through;
      margin-left: 10px;
    }
    
    /* Footer */
    .footer {
      background: var(--primary);
      color: white;
      padding: 40px 0;
      text-align: center;
    }
    
    .footer-text {
      margin-bottom: 10px;
    }
    
    .footer-links {
      display: flex;
      justify-content: center;
      gap: 20px;
    }
    
    .footer-links a {
      color: white;
      text-decoration: none;
      transition: var(--transition);
    }
    
    .footer-links a:hover {
      color: var(--secondary);
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
      .product-container {
        grid-template-columns: 1fr;
        gap: 40px;
      }
      
      .product-gallery {
        position: static;
      }
      
      .main-image {
        height: 400px;
      }
    }
    
    @media (max-width: 768px) {
      .product-title {
        font-size: 28px;
      }
      
      .current-price {
        font-size: 24px;
      }
      
      .product-actions {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
      
      .tabs-header {
        overflow-x: auto;
        scrollbar-width: thin;
        scrollbar-color: var(--secondary) var(--light);
      }
      
      .tabs-header::-webkit-scrollbar {
        height: 6px;
      }
      
      .tabs-header::-webkit-scrollbar-track {
        background: var(--light);
      }
      
      .tabs-header::-webkit-scrollbar-thumb {
        background-color: var(--secondary);
      }
      
      .tab-btn {
        white-space: nowrap;
      }
      
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
      }
    }
    
    @media (max-width: 576px) {
      .header-content {
        padding: 10px 0;
      }
      
      .logo {
        font-size: 20px;
      }
      
      .header-icons {
        gap: 15px;
      }
      
      .product-title {
        font-size: 24px;
      }
      
      .current-price {
        font-size: 20px;
      }
      
      .thumbnail-container {
        grid-template-columns: repeat(3, 1fr);
      }
      
      .section-title {
        font-size: 28px;
      }
      
      .products-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <div class="header-content">
        <a href="../index.php" class="logo">SLAY<span>MART</span></a>
        <div class="header-icons">
          <a href="#" class="header-icon">
            <i class="far fa-heart"></i>
          </a>
          <a href="#" class="header-icon">
            <i class="far fa-user"></i>
          </a>
          <a href="../cart/index.php" class="header-icon">
            <i class="fas fa-shopping-bag"></i>
            <span class="cart-count">3</span>
          </a>
        </div>
      </div>
    </div>
  </header>
  
  <!-- Breadcrumb -->
  <div class="container">
    <div class="breadcrumb">
      <div class="breadcrumb-item"><a href="../index.php">Home</a></div>
      <div class="breadcrumb-item"><a href="../index.php?category=<?= urlencode($product['category']) ?>"><?= htmlspecialchars($product['category']) ?></a></div>
      <div class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></div>
    </div>
  </div>
  
  <!-- Product Section -->
  <section class="product-section">
    <div class="container">
      <div class="product-container">
        <!-- Product Gallery -->
        <div class="product-gallery">
          <img src="../images/uploads/<?= $images[0] ?? 'no-image.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image" id="mainImage">
          <div class="thumbnail-container">
            <?php foreach ($images as $i => $img): ?>
              <img src="../images/uploads/<?= $img ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="thumbnail <?= $i == 0 ? 'active' : '' ?>" data-image="../images/uploads/<?= $img ?>">
            <?php endforeach; ?>
          </div>
        </div>
        
        <!-- Product Details -->
        <div class="product-details">
          <span class="product-badge">Exclusive</span>
          <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
          
          <div class="product-rating">
            <div class="stars">
              <?php
              $rating = $product['avg_rating'] ?? 0;
              for ($i = 1; $i <= 5; $i++):
                echo '<i class="fas fa-star"></i>';
              endfor;
              ?>
            </div>
            <span class="rating-value"><?= $rating ?> (24 Reviews)</span>
          </div>
          
          <div class="product-price">
            <span class="current-price">PKR <?= number_format($final_price) ?></span>
            <?php if ($product['discount'] > 0): ?>
              <span class="original-price">PKR <?= number_format($product['price']) ?></span>
              <span class="discount-badge">Save <?= $product['discount'] ?>%</span>
            <?php endif; ?>
          </div>
          
          <div class="product-description">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
          </div>
          
          <div class="product-options">
            <!-- Colors -->
            <?php if (mysqli_num_rows($color_res) > 0): ?>
              <div class="option-title">Color</div>
              <div class="color-options">
                <?php while ($c = mysqli_fetch_assoc($color_res)): ?>
                  <div class="color-option active" style="background-color: <?= $c['name'] ?>"></div>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
            
            <!-- Sizes -->
            <?php if (mysqli_num_rows($size_res) > 0): ?>
              <div class="option-title">Size</div>
              <div class="size-options">
                <?php while ($s = mysqli_fetch_assoc($size_res)): ?>
                  <div class="size-option active"><?= $s['name'] ?></div>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="product-actions">
            <a href="../add-to-cart/index.php?add=<?= $product['id'] ?>" class="btn btn-primary">
              <i class="fas fa-shopping-cart"></i> Add to Cart
            </a>
            <a href="../checkout/buy_now.php?id=<?= $product['id'] ?>" class="btn btn-secondary">
              <i class="fas fa-bolt"></i> Buy Now
            </a>
          </div>
          
          <div class="product-meta">
            <div class="meta-item">
              <div class="meta-icon">
                <i class="fas fa-truck"></i>
              </div>
              <div class="meta-text">Free Shipping & Returns</div>
            </div>
            <div class="meta-item">
              <div class="meta-icon">
                <i class="fas fa-shield-alt"></i>
              </div>
              <div class="meta-text">1 Year Warranty</div>
            </div>
            <div class="meta-item">
              <div class="meta-icon">
                <i class="fas fa-undo"></i>
              </div>
              <div class="meta-text">30 Days Return Policy</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  
  <!-- Tabs Section -->
  <section class="tabs-section">
    <div class="container">
      <div class="tabs-header">
        <button class="tab-btn active" data-tab="description">Description</button>
        <button class="tab-btn" data-tab="specifications">Specifications</button>
        <button class="tab-btn" data-tab="reviews">Reviews (<?= mysqli_num_rows($review_res) ?>)</button>
        <button class="tab-btn" data-tab="shipping">Shipping</button>
      </div>
      
      <div class="tab-content active" id="description">
        <h3 style="margin-bottom: 20px;">Product Description</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p>Our exclusive products are crafted with the finest materials and attention to detail. Each piece is designed to offer both style and functionality, making it the perfect addition to your collection.</p>
        <p>Experience luxury like never before with our premium range of products that are built to last and impress.</p>
      </div>
      
      <div class="tab-content" id="specifications">
        <h3 style="margin-bottom: 20px;">Specifications</h3>
        <table style="width: 100%; border-collapse: collapse;">
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 0; font-weight: 600;">Brand</td>
            <td style="padding: 12px 0;">Slaymart</td>
          </tr>
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 0; font-weight: 600;">Material</td>
            <td style="padding: 12px 0;">Premium Quality</td>
          </tr>
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 0; font-weight: 600;">Dimensions</td>
            <td style="padding: 12px 0;">Varies by product</td>
          </tr>
          <tr style="border-bottom: 1px solid #eee;">
            <td style="padding: 12px 0; font-weight: 600;">Warranty</td>
            <td style="padding: 12px 0;">1 Year</td>
          </tr>
        </table>
      </div>
      
      <div class="tab-content" id="reviews">
        <h3 style="margin-bottom: 20px;">Customer Reviews</h3>
        <?php if (mysqli_num_rows($review_res) > 0): ?>
          <?php while ($review = mysqli_fetch_assoc($review_res)): ?>
            <div class="review-item">
              <div class="review-header">
                <div class="reviewer-name"><?= htmlspecialchars($review['user_name']) ?></div>
                <div class="review-date"><?= date('d M Y', strtotime($review['created_at'])) ?></div>
              </div>
              <div class="review-rating">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="fas fa-star<?= $i <= $review['rating'] ? '' : '-o' ?>"></i>
                <?php endfor; ?>
              </div>
              <div class="review-text"><?= nl2br(htmlspecialchars($review['feedback'])) ?></div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-reviews">
            <i class="fas fa-comments"></i>
            <p>No reviews yet. Be the first to review this product!</p>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="tab-content" id="shipping">
        <h3 style="margin-bottom: 20px;">Shipping & Returns</h3>
        <p>We offer free shipping on all orders over PKR 5,000. Standard delivery takes 3-5 business days, while express delivery takes 1-2 business days.</p>
        <p>If you're not completely satisfied with your purchase, you can return it within 30 days for a full refund or exchange. Please ensure the product is in its original condition with all tags attached.</p>
        <p>For more information, please refer to our <a href="#" style="color: var(--secondary);">Shipping Policy</a> and <a href="#" style="color: var(--secondary);">Return Policy</a>.</p>
      </div>
    </div>
  </section>
  
  <!-- Related Products -->
  <section class="related-section">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">You May Also Like</h2>
        <p class="section-subtitle">Discover more exclusive products from our premium collection</p>
      </div>
      
      <div class="products-grid">
        <?php if (mysqli_num_rows($related_res) > 0): ?>
          <?php while ($related = mysqli_fetch_assoc($related_res)): 
            // Calculate discount price for related product
            $related_final_price = $related['discount'] > 0 
              ? $related['price'] - ($related['price'] * $related['discount'] / 100) 
              : $related['price'];
              
            // Get first image for related product
            $related_img_sql = "SELECT image FROM product_images WHERE product_id = {$related['id']} LIMIT 1";
            $related_img_result = mysqli_query($conn, $related_img_sql);
            $related_img = mysqli_fetch_assoc($related_img_result);
            $related_image = $related_img['image'] ?? 'no-image.png';
          ?>
            <div class="product-card">
              <div class="product-card-img">
                <img src="../images/uploads/<?= $related_image ?>" alt="<?= htmlspecialchars($related['name']) ?>">
              </div>
              <div class="product-card-body">
                <h3 class="product-card-title"><?= htmlspecialchars($related['name']) ?></h3>
                <div class="product-card-price">
                  <?php if ($related['discount'] > 0): ?>
                    PKR <?= number_format($related_final_price) ?>
                    <span class="product-card-original-price">PKR <?= number_format($related['price']) ?></span>
                  <?php else: ?>
                    PKR <?= number_format($related['price']) ?>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="no-reviews">
            <i class="fas fa-box-open"></i>
            <p>No related products found.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  
  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p class="footer-text">&copy; <?= date('Y') ?> Slaymart. All rights reserved.</p>
      <div class="footer-links">
        <a href="#">Privacy Policy</a>
        <a href="#">Terms of Service</a>
        <a href="#">Contact Us</a>
      </div>
    </div>
  </footer>
  
  <script>
    // Tab functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        const tabId = btn.getAttribute('data-tab');
        
        // Remove active class from all buttons and contents
        tabBtns.forEach(b => b.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        
        // Add active class to clicked button and corresponding content
        btn.classList.add('active');
        document.getElementById(tabId).classList.add('active');
      });
    });
    
    // Thumbnail functionality
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.getElementById('mainImage');
    
    thumbnails.forEach(thumbnail => {
      thumbnail.addEventListener('click', () => {
        // Remove active class from all thumbnails
        thumbnails.forEach(t => t.classList.remove('active'));
        
        // Add active class to clicked thumbnail
        thumbnail.classList.add('active');
        
        // Change main image
        mainImage.src = thumbnail.getAttribute('data-image');
      });
    });
    
    // Color and size selection
    const colorOptions = document.querySelectorAll('.color-option');
    const sizeOptions = document.querySelectorAll('.size-option');
    
    colorOptions.forEach(option => {
      option.addEventListener('click', () => {
        colorOptions.forEach(o => o.classList.remove('active'));
        option.classList.add('active');
      });
    });
    
    sizeOptions.forEach(option => {
      option.addEventListener('click', () => {
        sizeOptions.forEach(o => o.classList.remove('active'));
        option.classList.add('active');
      });
    });
  </script>
</body>
</html>