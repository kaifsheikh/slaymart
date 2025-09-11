<?php
include "../assets/css/bootstrap_files.html";
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

// ✅ Get product ID
$product_id = $_GET['product_id'] ?? 0;

// ✅ Verify product
$product_check = $conn->prepare("SELECT id, name FROM products WHERE id = ?");
$product_check->bind_param("i", $product_id);
$product_check->execute();
$product_result = $product_check->get_result();

if ($product_result->num_rows === 0) {
    header("Location: ../index.php?error=Product not found");
    exit();
}
$product_info = $product_result->fetch_assoc();

// ✅ Get product first image
$product_image_query = $conn->prepare("SELECT image FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1");
$product_image_query->bind_param("i", $product_id);
$product_image_query->execute();
$product_image_result = $product_image_query->get_result();
$product_image_data = $product_image_result->fetch_assoc();
$product_image = $product_image_data['image'] ?? "placeholder.png";

// ✅ Reviews fetch
$stmt = $conn->prepare("SELECT u.name AS fullname, r.rating, r.feedback, r.created_at
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        WHERE r.product_id = ?
                        ORDER BY r.created_at DESC");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Rating calculation
$total_reviews = $result->num_rows;
$avg_rating = 0;
$rating_distribution = [1=>0,2=>0,3=>0,4=>0,5=>0];

if ($total_reviews > 0) {
    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = $row;
        $rating_distribution[$row['rating']]++;
    }
    $rating_sum = array_sum(array_column($ratings, 'rating'));
    $avg_rating = round($rating_sum / $total_reviews, 1);
} else {
    $ratings = [];
}

// ✅ Rating percentages
$rating_percentages = [];
foreach ($rating_distribution as $star => $count) {
    $rating_percentages[$star] = $total_reviews > 0 ? round(($count / $total_reviews) * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- SEO Meta Tags -->
  <title>Customer Reviews for <?php echo htmlspecialchars($product_info['name']); ?> - Slaymart</title>
  <meta name="description" content="Read authentic customer reviews and ratings for <?php echo htmlspecialchars($product_info['name']); ?>. High-quality material, fast delivery, and trusted by customers across Pakistan.">
  <meta name="keywords" content="<?php echo htmlspecialchars($product_info['name']); ?> reviews, customer feedback, ratings, online shopping Pakistan">
  <meta name="author" content="Slaymart">
  <!-- Social Media Open Graph Tags -->
  <meta property="og:title" content="Customer Reviews for <?php echo htmlspecialchars($product_info['name']); ?> - Slaymart">
  <meta property="og:description" content="Check what our customers are saying about <?php echo htmlspecialchars($product_info['name']); ?>. Premium quality with free delivery in Pakistan.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.slaymart.com/reviews?product_id=<?php echo $product_id; ?>">
  <meta property="og:image" content="https://www.slaymart.com/assets/images/review-banner.jpg">
  <!-- Favicon -->
  <link rel="icon" href="../assets/images/favicon.png" type="image/png">
  
  <!-- External CSS -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
      min-height: 100vh;
      padding: 20px;
      color: #333;
    }
    .reviews-container {
      max-width: 1000px;
      margin: 0 auto;
    }
    .page-header {
      text-align: center;
      margin-bottom: 40px;
      position: relative;
    }
    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      color: #2c3e50;
      margin-bottom: 15px;
      display: inline-block;
      position: relative;
    }
    .page-title::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 4px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 2px;
    }
    .page-subtitle {
      color: #6c757d;
      font-size: 1.1rem;
      max-width: 600px;
      margin: 0 auto;
    }
    .product-info {
      background: white;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      display: flex;
      align-items: center;
    }
    .product-image {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 10px;
      margin-right: 20px;
    }
    .product-details h3 {
      font-weight: 600;
      margin-bottom: 5px;
      color: #2c3e50;
    }
    .product-details p {
      color: #6c757d;
      font-size: 0.9rem;
      margin-bottom: 0;
    }
    .reviews-stats {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-bottom: 40px;
      flex-wrap: wrap;
    }
    .stat-card {
      background: white;
      border-radius: 15px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      min-width: 150px;
      transition: all 0.3s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #667eea;
      margin-bottom: 5px;
    }
    .stat-label {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .review-card {
      background: white;
      border-radius: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 25px;
      overflow: hidden;
      transition: all 0.3s ease;
    }
    .review-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    .review-header {
      padding: 20px;
      border-bottom: 1px solid #f1f3f5;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    .reviewer-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 600;
      flex-shrink: 0;
    }
    .reviewer-info {
      flex-grow: 1;
    }
    .reviewer-name {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 5px;
    }
    .review-date {
      color: #6c757d;
      font-size: 0.9rem;
    }
    .review-rating {
      color: #ffc107;
      font-size: 1.1rem;
    }
    .review-body {
      padding: 20px;
    }
    .feedback-text {
      color: #495057;
      line-height: 1.6;
      margin-bottom: 15px;
    }
    .review-actions {
      display: flex;
      gap: 15px;
    }
    .action-btn {
      display: flex;
      align-items: center;
      gap: 5px;
      color: #6c757d;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }
    .action-btn:hover {
      color: #667eea;
    }
    .no-reviews {
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .no-reviews-icon {
      font-size: 4rem;
      color: #dee2e6;
      margin-bottom: 20px;
    }
    .no-reviews h3 {
      font-weight: 600;
      margin-bottom: 15px;
      color: #2c3e50;
    }
    .no-reviews p {
      color: #6c757d;
      max-width: 500px;
      margin: 0 auto 20px;
    }
    .action-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 40px;
    }
    .btn {
      border-radius: 10px;
      font-weight: 600;
      padding: 12px 25px;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-primary {
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: white;
      border: none;
    }
    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
      color: white;
    }
    .btn-outline-secondary {
      background: white;
      color: #6c757d;
      border: 1px solid #dee2e6;
    }
    .btn-outline-secondary:hover {
      background: #f8f9fa;
      color: #495057;
    }
    .rating-breakdown {
      background: white;
      border-radius: 15px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .rating-title {
      font-weight: 600;
      margin-bottom: 15px;
      color: #2c3e50;
    }
    .rating-bar {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }
    .rating-bar:last-child {
      margin-bottom: 0;
    }
    .rating-label {
      width: 60px;
      font-size: 0.9rem;
    }
    .bar-container {
      flex-grow: 1;
      height: 10px;
      background: #e9ecef;
      border-radius: 5px;
      margin: 0 15px;
      overflow: hidden;
    }
    .bar-fill {
      height: 100%;
      background: linear-gradient(135deg, #667eea, #764ba2);
      border-radius: 5px;
    }
    .rating-count {
      width: 40px;
      text-align: right;
      font-size: 0.9rem;
      color: #6c757d;
    }
    .average-rating {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
    }
    .average-number {
      font-size: 3rem;
      font-weight: 700;
      color: #667eea;
      margin-right: 15px;
    }
    .average-stars {
      font-size: 1.5rem;
      color: #ffc107;
    }
    /* Responsive Design */
    @media (max-width: 768px) {
      .page-title {
        font-size: 2rem;
      }
      
      .reviews-stats {
        gap: 15px;
      }
      
      .stat-card {
        min-width: 120px;
        padding: 15px;
      }
      
      .stat-number {
        font-size: 1.5rem;
      }
      
      .review-header {
        padding: 15px;
      }
      
      .reviewer-avatar {
        width: 50px;
        height: 50px;
        font-size: 1.2rem;
      }
      
      .review-body {
        padding: 15px;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .btn {
        width: 100%;
      }
      
      .product-info {
        flex-direction: column;
        text-align: center;
      }
      
      .product-image {
        margin-right: 0;
        margin-bottom: 15px;
      }
    }
    @media (max-width: 576px) {
      body {
        padding: 10px;
      }
      
      .page-title {
        font-size: 1.8rem;
      }
      
      .stat-card {
        min-width: 100px;
        padding: 12px;
      }
      
      .stat-number {
        font-size: 1.3rem;
      }
      
      .average-number {
        font-size: 2.5rem;
      }
      
      .average-stars {
        font-size: 1.2rem;
      }
    }
  </style>
</head>
<body>
  <div class="reviews-container">
    <div class="page-header">
      <h1>Customer Reviews</h1>
      <p>See what our customers are saying about our products</p>
    </div>

    <!-- Product Info -->
    <div class="product-info">
      <img src="../images/uploads/<?= $product_image ?>" alt="<?= htmlspecialchars($product_info['name']) ?>" class="product-image">
      <div>
        <h3><?= htmlspecialchars($product_info['name']) ?></h3>
        <p>Read what our customers are saying about this product</p>
      </div>
    </div>

    <!-- Average Rating -->
    <div class="average-rating">
      <div class="average-number"><?= $avg_rating ?></div>
      <div class="average-stars">
        <?php for($i=1;$i<=5;$i++): ?>
          <?php if($i <= round($avg_rating)): ?>
            <i class="fas fa-star"></i>
          <?php else: ?>
            <i class="far fa-star"></i>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Stats -->
    <div class="reviews-stats">
      <div class="stat-card"><div class="stat-number"><?= $total_reviews ?></div><div>Reviews</div></div>
      <div class="stat-card"><div class="stat-number"><?= $avg_rating ?></div><div>Avg Rating</div></div>
      <div class="stat-card"><div class="stat-number"><?= $rating_percentages[4]+$rating_percentages[5] ?>%</div><div>Satisfied</div></div>
    </div>

    <!-- Rating Breakdown -->
    <div class="rating-breakdown">
      <h3>Rating Breakdown</h3>
      <?php for($i=5;$i>=1;$i--): ?>
        <div class="rating-bar">
          <div><?= $i ?> ⭐</div>
          <div class="bar-container">
            <div class="bar-fill" style="width: <?= $rating_percentages[$i] ?>%"></div>
          </div>
          <div><?= $rating_percentages[$i] ?>%</div>
        </div>
      <?php endfor; ?>
    </div>

    <!-- Reviews List -->
    <?php if($total_reviews > 0): ?>
      <?php foreach($ratings as $row): ?>
        <div class="review-card">
          <div class="review-header">
            <div class="reviewer-avatar"><?= strtoupper(substr($row['fullname'],0,1)) ?></div>
            <div>
              <div><strong><?= htmlspecialchars($row['fullname']) ?></strong></div>
              <div><?= date("d M, Y", strtotime($row['created_at'])) ?></div>
            </div>
            <div class="review-rating">
              <?php for($i=1;$i<=5;$i++): ?>
                <?php if($i <= $row['rating']): ?>
                  <i class="fas fa-star"></i>
                <?php else: ?>
                  <i class="far fa-star"></i>
                <?php endif; ?>
              <?php endfor; ?>
            </div>
          </div>
          <div class="review-body">
            <p class="feedback-text"><?= nl2br(htmlspecialchars($row['feedback'])) ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-reviews">
        <h3>No Reviews Yet</h3>
        <p>Be the first to share your experience with this product.</p>
      </div>
    <?php endif; ?>

  
  </div>
</body>
</html>