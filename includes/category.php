<?php
$categoryResult = $conn->query("SELECT category, COUNT(*) AS product_count FROM products WHERE type = 'normal' AND category IS NOT NULL AND category <> '' GROUP BY category ORDER BY category ASC");
$categories = $categoryResult ? $categoryResult->fetch_all(MYSQLI_ASSOC) : [];
?>
<section class="store-categories container" aria-labelledby="category-heading">
    <div class="section-heading"><div><p class="eyebrow">Shop by collection</p><h2 class="title" id="category-heading">Explore categories</h2></div><button class="category-link" type="button" data-category="all">View all <ion-icon name="arrow-forward-outline"></ion-icon></button></div>
    <div class="category-scroller" role="list">
        <?php foreach ($categories as $category): ?>
            <button class="category-chip" type="button" data-category="<?= e($category['category']) ?>" role="listitem"><span class="category-chip-icon"><ion-icon name="bag-handle-outline"></ion-icon></span><span><strong><?= e($category['category']) ?></strong><small><?= (int) $category['product_count'] ?> products</small></span><ion-icon name="arrow-forward-outline"></ion-icon></button>
        <?php endforeach; ?>
    </div>
</section>
