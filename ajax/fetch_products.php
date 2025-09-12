<?php
include '../config/db.php';

$category = $_GET['category'] ?? '';
$category_safe = mysqli_real_escape_string($conn, $category);

if ($category_safe === 'all') {
  $query = "SELECT p.*, ROUND(AVG(r.rating),1) AS avg_rating 
            FROM products p 
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.type = 'normal'
            GROUP BY p.id
            ORDER BY p.id DESC";
} else {
  $query = "SELECT p.*, ROUND(AVG(r.rating),1) AS avg_rating 
            FROM products p 
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.category = '$category_safe' 
              AND p.type = 'normal'
            GROUP BY p.id
            ORDER BY p.id DESC";
}


$result = mysqli_query($conn, $query);
?>

<div class="product-main">
  <h2 class="title">
    <?= $category_safe === 'all' ? 'All Products' : htmlspecialchars($category_safe) . ' Products' ?>
  </h2>

  <div class="product-grid">

    <?php while ($row = mysqli_fetch_assoc($result)): ?>
      <?php
      // Get product images
      $img_sql = "SELECT image FROM product_images WHERE product_id = {$row['id']} ORDER BY id ASC LIMIT 2";
      $img_res = mysqli_query($conn, $img_sql);
      $images = [];
      while ($img = mysqli_fetch_assoc($img_res)) {
        $images[] = $img['image'];
      }
      $default_img = $images[0] ?? "no-image.png";
      $hover_img   = $images[1] ?? $default_img;

      // Price calculation
      $original_price = $row['price'];
      $discount = $row['discount'];
      $discounted_price = $original_price - ($original_price * $discount / 100);

      // Rating
      $rating = $row['avg_rating'] ?? 0;
      ?>
      <div class="showcase">
        <div class="showcase-banner">
          <a href="./product-detail/index.php?id=<?= $row['id'] ?>">
            <img src="./images/uploads/<?= $default_img ?>"
              alt="<?= htmlspecialchars($row['name']) ?>"
              width="300" class="product-img default">

            <img src="./images/uploads/<?= $hover_img ?>"
              alt="<?= htmlspecialchars($row['name']) ?>"
              width="300" class="product-img hover">
          </a>

          <?php if ($discount > 0): ?>
            <p class="showcase-badge"><?= $discount ?>% OFF</p>
          <?php endif; ?>

          <div class="showcase-actions">
            <!-- Product Detail Page -->
            <a href="./product-detail/index.php?id=<?= $row['id'] ?>">
              <button class="btn-action">
                <ion-icon name="document-text-outline"></ion-icon>
              </button>
            </a>
          </div>
        </div>

        <div class="showcase-content">
          <a href="#" class="showcase-category"><?= htmlspecialchars($row['category']) ?></a>

          <a href="./product-detail/index.php?id=<?= $row['id'] ?>">
            <h3 class="showcase-title"><?= htmlspecialchars($row['name']) ?></h3>
          </a>

          <div class="showcase-rating">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <ion-icon name="<?= $i <= $rating ? 'star' : 'star-outline' ?>"></ion-icon>
            <?php endfor; ?>
          </div>

          <div class="price-box">
            <?php if ($discount > 0): ?>
              <p class="price">PKR <?= number_format($discounted_price) ?></p>
              <del>PKR <?= number_format($original_price) ?></del>
            <?php else: ?>
              <p class="price">PKR <?= number_format($original_price) ?></p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>

  </div>
</div>