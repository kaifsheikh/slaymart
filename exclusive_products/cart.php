<?php
include "../config/db.php"; // apna db path sahi karna
$query = "SELECT p.*, ROUND(AVG(r.rating),1) AS avg_rating
            FROM products p
            LEFT JOIN reviews r ON p.id = r.product_id
            WHERE p.type = 'exclusive'
            GROUP BY p.id
            ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaymart Premium - Exclusive Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Amazon Ember', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
        }

        body {
            background-color: #f7f8f8;
            color: #0f1111;
        }

        /* Premium Header */
        .premium-header {
            background: linear-gradient(135deg, #232f3e 0%, #131921 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #ff9900;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .premium-badge {
            background: #ff9900;
            color: #131921;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header-nav {
            display: flex;
            gap: 25px;
        }

        .header-nav a {
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }

        .header-nav a:hover {
            color: #ff9900;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 4px;
            overflow: hidden;
            width: 300px;
        }

        .search-bar input {
            flex: 1;
            border: none;
            padding: 8px 12px;
            font-size: 0.9rem;
            outline: none;
        }

        .search-bar button {
            background: #ff9900;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            color: #131921;
            font-weight: bold;
        }

        .account-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .account-section a {
            color: white;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-size: 0.8rem;
        }

        .account-section i {
            font-size: 1.2rem;
            margin-bottom: 3px;
        }

        /* Premium Products Section */
        .premium-products {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e7e9ec;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f1111;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 5px;
            height: 25px;
            background: #ff9900;
            border-radius: 3px;
        }

        .filter-options {
            display: flex;
            gap: 15px;
        }

        .filter-btn {
            background: white;
            border: 1px solid #d5d9d9;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .filter-btn:hover {
            background: #f7f8f8;
            border-color: #a2a6ac;
        }

        /* Product Grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* Product Card - Enhanced Amazon Style */
        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #e7e9ec;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.12);
            border-color: #d5d9d9;
            transform: translateY(-3px);
        }

        /* Product Badge */
        .product-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff9900;
            color: #131921;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            z-index: 10;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Product Image */
        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
            background: #f7f8f8;
            border-bottom: 1px solid #e7e9ec;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-link {
            display: block;
            height: 100%;
            width: 100%;
        }

        .product-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: opacity 0.3s ease;
        }

        .product-img.hover {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
        }

        .product-image-container:hover .product-img.default {
            opacity: 0;
        }

        .product-image-container:hover .product-img.hover {
            opacity: 1;
        }

        /* Quick Actions */
        .quick-actions {
            position: absolute;
            bottom: 10px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 10px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-image-container:hover .quick-actions {
            opacity: 1;
        }

        .action-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            border: 1px solid #d5d9d9;
            cursor: pointer;
        }

        .action-btn:hover {
            background: #ff9900;
            transform: scale(1.05);
        }

        .action-btn ion-icon {
            font-size: 1.1rem;
            color: #131921;
            transition: color 0.2s ease;
        }

        /* Product Content */
        .product-content {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-category {
            color: #565959;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: 400;
        }

        .product-title {
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 8px;
            color: #0f1111;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 2.6rem;
            text-decoration: none;
        }

        .product-title:hover {
            color: #c45500;
            text-decoration: underline;
        }

        .product-rating {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 8px;
        }

        .product-rating ion-icon {
            color: #ff9900;
            font-size: 0.9rem;
        }

        .rating-value {
            font-weight: 400;
            color: #565959;
            margin-left: 5px;
            font-size: 0.8rem;
        }

        .product-description {
            color: #565959;
            font-size: 0.85rem;
            line-height: 1.4;
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Product Options */
        .product-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 12px;
        }

        .color-options {
            display: flex;
            gap: 6px;
        }

        .color-dot {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 1px solid #d5d9d9;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .color-dot:hover {
            transform: scale(1.1);
            border-color: #a2a6ac;
        }

        .size-options {
            display: flex;
            gap: 6px;
        }

        .size-badge {
            background: #f7f8f8;
            color: #565959;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border: 1px solid #d5d9d9;
        }

        .size-badge:hover {
            background: #e7e9ec;
            border-color: #a2a6ac;
        }

        /* Product Footer */
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            margin-bottom: 15px;
        }

        .stock-status {
            display: flex;
            align-items: center;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .in-stock {
            color: #007600;
        }

        .in-stock .status-dot {
            background: #007600;
        }

        .out-stock {
            color: #b12704;
        }

        .out-stock .status-dot {
            background: #b12704;
        }

        .price-container {
            text-align: right;
        }

        .current-price,
        .discount-price {
            font-weight: 700;
            font-size: 1.1rem;
            color: #0f1111;
        }

        .discount-price {
            color: #b12704;
        }

        .original-price {
            font-size: 0.8rem;
            color: #565959;
            text-decoration: line-through;
            margin-left: 8px;
        }

        /* Action Buttons */
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .btn {
            flex: 1;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.85rem;
            text-transform: capitalize;
            letter-spacing: 0.3px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
        }

        .btn.add-to-cart {
            background: #f7f8f8;
            color: #0f1111;
            border: 1px solid #d5d9d9;
        }

        .btn.add-to-cart:hover {
            background: #e7e9ec;
            border-color: #a2a6ac;
        }

        .btn.buy-now {
            background: linear-gradient(to bottom, #f7dfa5, #ff9900);
            color: #131921;
            border: 1px solid #a88734;
        }

        .btn.buy-now:hover {
            background: linear-gradient(to bottom, #f5d78e, #eeba37);
            border-color: #a88734;
        }

        .btn ion-icon {
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
                gap: 20px;
            }
        }

        @media (max-width: 992px) {
            .header-nav {
                display: none;
            }

            .search-bar {
                width: 200px;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 20px;
            }

            .product-image-container {
                height: 200px;
            }
        }

        @media (max-width: 768px) {
            .premium-products {
                padding: 0 15px;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .filter-options {
                display: none;
            }

            .product-grid {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
                gap: 15px;
            }

            .product-image-container {
                height: 180px;
            }

            .product-content {
                padding: 12px;
            }

            .product-title {
                font-size: 0.95rem;
                height: 2.5rem;
            }

            .btn {
                padding: 8px 12px;
                font-size: 0.8rem;
            }

            .btn ion-icon {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                flex-wrap: wrap;
            }

            .logo-section {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }

            .search-bar {
                width: 100%;
                order: 3;
                margin-top: 10px;
            }

            .premium-products {
                padding: 0 10px;
            }

            .product-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .product-image-container {
                height: 220px;
            }

            .product-content {
                height: auto;
            }

            .product-options {
                flex-direction: column;
                gap: 8px;
            }

            .product-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .price-container {
                text-align: left;
            }

            .product-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Premium Header -->
    <header class="premium-header">
        <div class="header-container">
            <div class="logo-section">
                <div class="logo">Slaymart</div>
                <div class="premium-badge">Premium</div>
            </div>

            <div class="header-search-container">
                <form action="../../search/search.php" method="GET">
                    <div class="search-bar">
                        <input type="search" name="search" class="search-field" placeholder="Search premium products...">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                    </div>
                </form>
            </div>

            <div class="header-nav">
                <a href="#"><i class="fas fa-list"></i> Categories</a>
                <a href="#"><i class="fas fa-percentage"></i> Deals</a>
                <a href="#"><i class="fas fa-gift"></i> Gift Cards</a>
            </div>

            <div class="account-section">
                <a href="#">
                    <i class="fas fa-user"></i>
                    <span>Account</span>
                </a>
                <a href="#">
                    <i class="fas fa-heart"></i>
                    <span>Wishlist</span>
                </a>
                <a href="#">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Cart</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Premium Products Section -->
    <section class="premium-products">
        <div class="section-header">
            <h2 class="section-title">Premium Exclusive Collection</h2>
            <div class="filter-options">
                <button class="filter-btn"><i class="fas fa-filter"></i> Filter</button>
                <button class="filter-btn"><i class="fas fa-sort"></i> Sort: Featured</button>
            </div>
        </div>

        <div class="product-grid">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <?php
                // --- Product Images ---
                $img_sql = "SELECT image FROM product_images WHERE product_id = {$row['id']} ORDER BY id ASC LIMIT 2";
                $img_res = mysqli_query($conn, $img_sql);
                $images = [];
                while ($img = mysqli_fetch_assoc($img_res)) {
                    $images[] = $img['image'];
                }
                $default_img = $images[0] ?? "no-image.png";
                $hover_img   = $images[1] ?? $default_img;

                // --- Colors ---
                $color_sql = "SELECT c.name, c.id FROM product_colors pc 
                              JOIN colors c ON pc.color_id = c.id 
                              WHERE pc.product_id = {$row['id']}";
                $color_res = mysqli_query($conn, $color_sql);

                // --- Sizes ---
                $size_sql = "SELECT s.name FROM product_sizes ps 
                             JOIN sizes s ON ps.size_id = s.id 
                             WHERE ps.product_id = {$row['id']}";
                $size_res = mysqli_query($conn, $size_sql);

                // --- Calculate Discount Price ---
                $final_price = $row['discount'] > 0 ? $row['price'] - ($row['price'] * $row['discount'] / 100) : $row['price'];
                ?>

                <div class="product-card">
                    <!-- Product Badge -->
                    <?php if ($row['discount'] > 0): ?>
                        <div class="product-badge">-<?= $row['discount'] ?>%</div>
                    <?php endif; ?>

                    <!-- Product Images -->
                    <div class="product-image-container">
                        <a href="./product_detail.php?id=<?= $row['id'] ?>" class="image-link">
                            <img src="../images/uploads/<?= $default_img ?>"
                                alt="<?= htmlspecialchars($row['name']) ?>"
                                class="product-img default">
                            <img src="../images/uploads/<?= $hover_img ?>"
                                alt="<?= htmlspecialchars($row['name']) ?>"
                                class="product-img hover">
                        </a>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <a href="../checkout/buy_now.php?id=<?= $row['id'] ?>" class="action-btn buy-btn" title="Buy Now">
                                <ion-icon name="flash-outline"></ion-icon>
                            </a>
                            <button class="action-btn" title="Add to Wishlist">
                                <ion-icon name="heart-outline"></ion-icon>
                            </button>
                        </div>
                    </div>

                    <!-- Product Content -->
                    <div class="product-content">
                        <div class="product-category"><?= htmlspecialchars($row['category']) ?></div>
                        <a href="./product_detail.php?id=<?= $row['id'] ?>" class="product-title"><?= htmlspecialchars($row['name']) ?></a>

                        <div class="product-rating">
                            <?php
                            $rating = $row['avg_rating'] ?? 0;
                            for ($i = 1; $i <= 5; $i++):
                            ?>
                                <ion-icon name="<?= $i <= $rating ? 'star' : 'star-outline' ?>"></ion-icon>
                            <?php endfor; ?>
                            <span class="rating-value"><?= $rating ?></span>
                        </div>

                        <div class="product-description"><?= substr($row['description'], 0, 80) ?>...</div>

                        <!-- Product Options -->
                        <div class="product-options">
                            <?php if (mysqli_num_rows($color_res) > 0): ?>
                                <div class="color-options">
                                    <?php while ($c = mysqli_fetch_assoc($color_res)): ?>
                                        <span class="color-dot" style="background-color: <?= $c['name'] ?>" title="<?= $c['name'] ?>"></span>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (mysqli_num_rows($size_res) > 0): ?>
                                <div class="size-options">
                                    <?php while ($s = mysqli_fetch_assoc($size_res)): ?>
                                        <span class="size-badge"><?= $s['name'] ?></span>
                                    <?php endwhile; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Stock & Price -->
                        <div class="product-footer">
                            <div class="stock-status <?= $row['stock_status'] == 'in' ? 'in-stock' : 'out-stock' ?>">
                                <span class="status-dot"></span>
                                <?= "STOCK" . strtoupper($row['stock_status']) ?>
                            </div>

                            <div class="price-container">
                                <?php if ($row['discount'] > 0): ?>
                                    <span class="discount-price">PKR <?= number_format($final_price) ?></span>
                                    <span class="original-price">PKR <?= number_format($row['price']) ?></span>
                                <?php else: ?>
                                    <span class="current-price">PKR <?= number_format($row['price']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="product-actions">
                            <a href="../checkout/buy_now.php?id=<?= $row['id'] ?>" class="btn buy-now">
                                <ion-icon name="flash-outline"></ion-icon> Buy Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

</body>

</html>