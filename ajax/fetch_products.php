<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/store_helpers.php';

header('Content-Type: text/html; charset=UTF-8');
$category = trim((string) ($_GET['category'] ?? 'all'));
$products = fetchStoreProducts($conn, $category);
$title = ($category === '' || $category === 'all') ? 'All products' : $category . ' products';
?>
<div class="product-main">
    <div class="section-heading"><div><p class="eyebrow">Curated for you</p><h2 class="title"><?= e($title) ?></h2></div><span class="product-count"><?= count($products) ?> items</span></div>
    <?php if ($products): ?>
        <div class="product-grid"><?php foreach ($products as $product): renderProductCard($product, '.'); endforeach; ?></div>
    <?php else: ?>
        <div class="store-empty"><ion-icon name="cube-outline"></ion-icon><h3>No products found</h3><p>Try another category or browse all products.</p></div>
    <?php endif; ?>
</div>