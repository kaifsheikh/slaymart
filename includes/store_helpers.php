<?php
/** Shared storefront helpers. Keep presentation and product calculations consistent. */

if (!function_exists('e')) {
    function e($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

function fetchStoreProducts(mysqli $conn, ?string $category = null): array
{
    $sql = "SELECT p.*, COALESCE(ROUND(AVG(r.rating), 1), 0) AS avg_rating,
                   GROUP_CONCAT(pi.image ORDER BY pi.id SEPARATOR '|') AS images
            FROM products p
            LEFT JOIN reviews r ON r.product_id = p.id
            LEFT JOIN product_images pi ON pi.product_id = p.id
            WHERE p.type = 'normal'";
    $types = '';
    $params = [];

    if ($category !== null && $category !== '' && $category !== 'all') {
        $sql .= ' AND p.category = ?';
        $types = 's';
        $params[] = $category;
    }

    $sql .= ' GROUP BY p.id ORDER BY p.id DESC';
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $stmt->close();

    return $products;
}

function renderProductCard(array $product, string $assetPrefix = '.'): void
{
    $images = array_values(array_filter(explode('|', (string) ($product['images'] ?? ''))));
    $defaultImage = $images[0] ?? 'placeholder.png';
    $hoverImage = $images[1] ?? $defaultImage;
    $price = max(0, (float) ($product['price'] ?? 0));
    $discount = min(100, max(0, (float) ($product['discount'] ?? 0)));
    $salePrice = $price * (1 - $discount / 100);
    $rating = min(5, max(0, (float) ($product['avg_rating'] ?? 0)));
    $detailUrl = $assetPrefix . '/product-detail/index.php?id=' . (int) $product['id'];
    $imageUrl = $assetPrefix . '/images/uploads/';
    ?>
    <article class="showcase product-card-modern">
        <div class="showcase-banner">
            <a href="<?= e($detailUrl) ?>" aria-label="View <?= e($product['name']) ?>">
                <img src="<?= e($imageUrl . $defaultImage) ?>" alt="<?= e($product['name']) ?>" class="product-img default" loading="lazy">
                <img src="<?= e($imageUrl . $hoverImage) ?>" alt="" class="product-img hover" loading="lazy">
            </a>
            <?php if ($discount > 0): ?><p class="showcase-badge"><?= e(rtrim(rtrim(number_format($discount, 2), '0'), '.')) ?>% OFF</p><?php endif; ?>
            <div class="showcase-actions"><a href="<?= e($detailUrl) ?>" class="btn-action" aria-label="View product"><ion-icon name="eye-outline"></ion-icon></a></div>
        </div>
        <div class="showcase-content">
            <p class="showcase-category"><?= e($product['category']) ?></p>
            <a href="<?= e($detailUrl) ?>"><h3 class="showcase-title"><?= e($product['name']) ?></h3></a>
            <div class="showcase-rating" aria-label="Rated <?= e(number_format($rating, 1)) ?> out of 5">
                <?php for ($star = 1; $star <= 5; $star++): ?><ion-icon name="<?= $star <= round($rating) ? 'star' : 'star-outline' ?>"></ion-icon><?php endfor; ?>
            </div>
            <div class="price-box"><p class="price">PKR <?= number_format($salePrice) ?></p><?php if ($discount > 0): ?><del>PKR <?= number_format($price) ?></del><?php endif; ?></div>
        </div>
    </article>
    <?php
}
