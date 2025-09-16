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
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 20px;
    }
    
    /* Header */
    .header {
      background: var(--amazon-blue);
      color: white;
      padding: 15px 0;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .header-content {
      display: flex;
      justify-content: center;
      align-items: center;
    }
    
    .logo {
      font-size: 28px;
      font-weight: 700;
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      transition: var(--transition);
    }
    
    .logo:hover {
      color: var(--amazon-light-orange);
    }
    
    .logo span {
      color: var(--amazon-light-orange);
    }
    
    /* Breadcrumb */
    .breadcrumb {
      padding: 15px 0;
      font-size: 14px;
      background: var(--amazon-light-gray);
      border-bottom: 1px solid var(--amazon-border);
    }
    
    .breadcrumb-item {
      display: inline-block;
    }
    
    .breadcrumb-item:not(:last-child)::after {
      content: ">";
      margin: 0 10px;
      color: var(--amazon-light-text);
    }
    
    .breadcrumb-item a {
      color: var(--amazon-light-text);
      text-decoration: none;
    }
    
    .breadcrumb-item a:hover {
      text-decoration: underline;
      color: var(--amazon-orange);
    }
    
    .breadcrumb-item.active {
      color: var(--amazon-text);
    }
    
    /* Product Section */
    .product-section {
      padding: 30px 0;
    }
    
    .product-container {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 40px;
    }
    
    /* Product Gallery */
    .product-gallery {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    
    .main-image-container {
      position: relative;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      overflow: hidden;
      background: white;
      height: 500px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .main-image {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
      transform-origin: center center;
    }
    
    .main-image-container:hover .main-image {
      transform: scale(1.5);
    }
    
    .thumbnail-container {
      display: flex;
      gap: 10px;
      overflow-x: auto;
      padding-bottom: 5px;
    }
    
    .thumbnail {
      min-width: 80px;
      height: 80px;
      object-fit: cover;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
    }
    
    .thumbnail:hover {
      border-color: var(--amazon-orange);
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .thumbnail.active {
      border: 2px solid var(--amazon-orange);
    }
    
    /* Product Details */
    .product-details {
      padding: 20px 0;
    }
    
    .product-title {
      font-size: 24px;
      font-weight: 400;
      margin-bottom: 10px;
      line-height: 1.3;
    }
    
    .product-badge {
      display: inline-block;
      background: var(--amazon-orange);
      color: var(--amazon-blue);
      padding: 3px 8px;
      border-radius: var(--border-radius);
      font-size: 12px;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .product-rating {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .stars {
      color: var(--amazon-star);
    }
    
    .rating-value {
      font-weight: 500;
      color: var(--amazon-light-text);
    }
  
    .product-price {
      margin-bottom: 20px;
    }
    
    .price-currency {
      font-size: 14px;
      vertical-align: super;
    }
    
    .price-whole {
      font-size: 28px;
      font-weight: 400;
    }
    
    .price-fraction {
      font-size: 16px;
      vertical-align: super;
    }
    
    .original-price {
      font-size: 14px;
      color: var(--amazon-light-text);
      text-decoration: line-through;
      margin-left: 10px;
    }
    
    .discount-badge {
      background: #CC0C39;
      color: white;
      padding: 2px 6px;
      border-radius: var(--border-radius);
      font-size: 12px;
      font-weight: 700;
      margin-left: 10px;
    }
    
    .product-description {
      color: var(--amazon-light-text);
      margin-bottom: 20px;
      line-height: 1.5;
    }
    
    .product-options {
      margin-bottom: 25px;
    }
    
    .option-title {
      font-size: 14px;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .color-options {
      display: flex;
      gap: 8px;
      margin-bottom: 20px;
    }
    
    .color-option {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      border: 2px solid transparent;
      transition: var(--transition);
    }
    
    .color-option:hover {
      border-color: var(--amazon-orange);
      transform: scale(1.1);
    }
    
    .size-options {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    
    .size-option {
      padding: 8px 12px;
      border: 1px solid var(--amazon-border);
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
      font-size: 14px;
      background: white;
    }
    
    .size-option:hover {
      border-color: var(--amazon-orange);
    }
     
    .product-actions {
      margin-bottom: 25px;
    }
    
    .btn {
      padding: 12px 20px;
      border-radius: var(--border-radius);
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: var(--transition);
      border: none;
      cursor: pointer;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .btn-primary {
      background: var(--amazon-orange);
      color: var(--amazon-blue);
      border: 1px solid var(--amazon-light-orange);
      border-radius: 20px;
    }
    
    .btn-primary:hover {
      background: var(--amazon-light-orange);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .btn-secondary {
      background: var(--amazon-light-gray);
      color: var(--amazon-text);
      border: 1px solid var(--amazon-border);
      border-radius: 20px;
    }
    
    .btn-secondary:hover {
      background: var(--amazon-border);
    }
    
    .product-meta {
      border-top: 1px solid var(--amazon-border);
      padding-top: 15px;
      margin-top: 15px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      margin-bottom: 12px;
      font-size: 14px;
    }
    
    .meta-icon {
      margin-right: 10px;
      color: var(--amazon-light-text);
    }
    
    .meta-text {
      color: var(--amazon-light-text);
    }
    
    .meta-text strong {
      color: var(--amazon-text);
    }
    
    /* Tabs Section */
    .tabs-section {
      padding: 30px 0;
      background: var(--amazon-light-gray);
      border-top: 1px solid var(--amazon-border);
    }
    
    .tabs-header {
      display: flex;
      border-bottom: 1px solid var(--amazon-border);
      margin-bottom: 20px;
      overflow-x: auto;
    }
    
    .tab-btn {
      padding: 12px 20px;
      background: none;
      border: none;
      font-weight: 700;
      color: var(--amazon-light-text);
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      font-size: 14px;
      white-space: nowrap;
      border-bottom: 3px solid transparent;
      margin-bottom: -1px;
    }
    
    .tab-btn:hover {
      color: var(--amazon-text);
      border-bottom-color: var(--amazon-border);
    }
    
    .tab-btn.active {
      color: var(--amazon-orange);
      border-bottom-color: var(--amazon-orange);
    }
    
    .tab-content {
      display: none;
      background: white;
      padding: 20px;
      border-radius: var(--border-radius);
      border: 1px solid var(--amazon-border);
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }
    
    .tab-content h3 {
      font-size: 20px;
      font-weight: 500;
      margin-bottom: 15px;
    }
    
    .tab-content p {
      color: var(--amazon-text);
      line-height: 1.6;
      margin-bottom: 15px;
    }
    
    /* Reviews */
    .review-item {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      margin-bottom: 15px;
      border: 1px solid var(--amazon-border);
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .review-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .reviewer-name {
      font-weight: 500;
      color: var(--amazon-text);
    }
    
    .review-date {
      color: var(--amazon-light-text);
      font-size: 14px;
    }
    
    .review-rating {
      color: var(--amazon-star);
      margin-bottom: 10px;
    }
    
    .review-text {
      color: var(--amazon-text);
      line-height: 1.5;
    }
    
    .no-reviews {
      text-align: center;
      padding: 30px;
      color: var(--amazon-light-text);
      background: white;
      border-radius: var(--border-radius);
      border: 1px solid var(--amazon-border);
    }
    
    .no-reviews i {
      font-size: 36px;
      color: var(--amazon-orange);
      margin-bottom: 10px;
    }
    
    /* Related Products */
   
    
   
    
  
    
    .product-card {
      background: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      border: 1px solid var(--amazon-border);
      transition: var(--transition);
    }
    
    .product-card:hover {
      border-color: var(--amazon-orange);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      transform: translateY(-5px);
    }
    
    .product-card-img {
      height: 200px;
      overflow: hidden;
      background: white;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .product-card-img img {
      max-width: 100%;
      max-height: 100%;
      object-fit: contain;
      transition: var(--transition);
    }
    
    .product-card:hover .product-card-img img {
      transform: scale(1.1);
    }
    
    .product-card-body {
      padding: 15px;
    }
    
    .product-card-title {
      font-size: 14px;
      font-weight: 400;
      margin-bottom: 8px;
      color: var(--amazon-text);
      height: 40px;
      overflow: hidden;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
    }
    
    .product-card-price {
      font-size: 16px;
      font-weight: 700;
      color: var(--amazon-text);
    }
    
    .product-card-original-price {
      font-size: 12px;
      color: var(--amazon-light-text);
      text-decoration: line-through;
      margin-left: 8px;
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
      .product-container {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      
      .main-image-container {
        height: 400px;
      }
    }
    
    @media (max-width: 768px) {
      .header-content {
        flex-wrap: wrap;
      }
      
      .product-title {
        font-size: 20px;
      }
      
      .btn {
        width: 100%;
        margin-bottom: 10px;
      }
      
    }
    
    @media (max-width: 576px) {
      .header-icon span {
        display: none;
      }
      
      .product-title {
        font-size: 18px;
      }
      
      .thumbnail-container {
        justify-content: center;
      }
      
      .section-title {
        font-size: 20px;
      }

    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <div class="header-content">
        <a href="../index.php" class="logo">
          <span>SLAYMART</span>
        </a>
      </div>
    </div>
  </header>
  
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <div class="container">
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
          <div class="main-image-container">
            <img src="../images/uploads/<?= $images[0] ?? 'no-image.png' ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image" id="mainImage">
          </div>
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
                if ($i <= floor($rating)) {
                  echo '<i class="fas fa-star"></i>';
                } elseif ($i - 0.5 <= $rating) {
                  echo '<i class="fas fa-star-half-alt"></i>';
                } else {
                  echo '<i class="far fa-star"></i>';
                }
              endfor;
              ?>
            </div>
            <span class="rating-value"><?= $rating ?></span>
          </div>
          
          <div class="product-price">
            <span class="price-currency">PKR</span>
            <span class="price-whole"><?= number_format($final_price, 0, '', '') ?></span>
            <span class="price-fraction">00</span>
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
              <div class="option-title">Color: </div>
              <div class="color-options">
                <?php while ($c = mysqli_fetch_assoc($color_res)): ?>
                  <div class="color-option active" style="background-color: <?= $c['name'] ?>" title="<?= $c['name'] ?>"></div>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
            
            <!-- Sizes -->
            <?php if (mysqli_num_rows($size_res) > 0): ?>
              <div class="option-title">Size: </div>
              <div class="size-options">
                <?php while ($s = mysqli_fetch_assoc($size_res)): ?>
                  <div class="size-option active"><?= $s['name'] ?></div>
                <?php endwhile; ?>
              </div>
            <?php endif; ?>
          </div>
          
          <div class="product-actions">
            <a href="../checkout/buy_now.php?id=<?= $product['id'] ?>" class="btn btn-primary">
              Buy Now
            </a>
          </div>
          
          <div class="product-meta">
            <div class="meta-item">
              <i class="fas fa-truck meta-icon"></i>
              <div class="meta-text"><strong>Standard delivery</strong> 250 PKR</div>
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
        <button class="tab-btn" data-tab="reviews">Reviews (<?= mysqli_num_rows($review_res) ?>)</button>
        <button class="tab-btn" data-tab="shipping">Shipping</button>
      </div>
      
      <div class="tab-content active" id="description">
        <h3>Product Description</h3>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <p>Our exclusive products are crafted with the finest materials and attention to detail. Each piece is designed to offer both style and functionality, making it the perfect addition to your collection.</p>
        <p>Experience luxury like never before with our premium range of products that are built to last and impress.</p>
      </div>
      
      <div class="tab-content" id="reviews">
        <h3>Customer Reviews</h3>
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
            <i class="far fa-comments"></i>
            <p>No reviews yet. Be the first to review this product!</p>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="tab-content" id="shipping">
        <h3>Shipping & Returns</h3>
        <p>We offer free shipping on all orders over PKR 5,000. Standard delivery takes 3-5 business days, while express delivery takes 1-2 business days.</p>
        <p>If you're not completely satisfied with your purchase, you can return it within 30 days for a full refund or exchange. Please ensure the product is in its original condition with all tags attached.</p>
        <p>For more information, please refer to our <a href="../../web-info/shipping.php" style="color: var(--amazon-orange);">Shipping Policy</a> and <a href="../../web-info/policy.php" style="color: var(--amazon-orange);">Return Policy</a>.</p>
      </div>
    </div>
  </section>
  
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
  </script>
</body>
</html>