<?php
require_once __DIR__ . '/store_helpers.php';
$products = fetchStoreProducts($conn);
?>
<main class="store-main">
    <section class="product-container">
        <div class="container">
            <aside class="sidebar" aria-label="Product categories"><div class="sidebar-category"><div class="sidebar-top"><h2 class="sidebar-title">Browse products</h2></div><div class="filter-list"><button class="filter-button is-active" type="button" data-category="all"><ion-icon name="grid-outline"></ion-icon> All products</button><?php foreach ($categories as $category): ?><button class="filter-button" type="button" data-category="<?= e($category['category']) ?>"><span><?= e($category['category']) ?></span><small><?= (int) $category['product_count'] ?></small></button><?php endforeach; ?></div></div></aside>
            <div class="product-box" id="product-list" aria-live="polite"><div class="product-main"><div class="section-heading"><div><p class="eyebrow">Fresh arrivals</p><h1 class="title">New products</h1></div><span class="product-count"><?= count($products) ?> items</span></div><?php if ($products): ?><div class="product-grid"><?php foreach ($products as $product): renderProductCard($product, '.'); endforeach; ?></div><?php else: ?><div class="store-empty"><ion-icon name="cube-outline"></ion-icon><h3>Products coming soon</h3><p>Please check back shortly.</p></div><?php endif; ?></div></div>
        </div>
    </section>
</main>
