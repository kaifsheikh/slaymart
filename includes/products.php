<!-- PRODUCTS -->
<style>
    /* Desktop styles */
    .product-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    /* Mobile styles */
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .showcase img {
            width: 100%;
            height: auto;
        }

        .showcase {
            padding: 5px;
        }

        .showcase-title {
            font-size: 14px;
        }

        .price-box .price,
        .price-box del {
            font-size: 13px;
        }

        .showcase-rating ion-icon {
            font-size: 14px;
        }
    }
</style>
<main>
    <div class="product-container" data-aos="zoom-in" data-aos-duration="400" data-aos-delay="100">
        <div class="container">

            <!-- SIDEBAR CATEGORY-->
            <div class="sidebar has-scrollbar" data-mobile-menu>
                <div class="sidebar-category">
                    <div class="sidebar-top">
                        <h2 class="sidebar-title">Category</h2>
                        <button class="sidebar-close-btn" data-mobile-menu-close-btn>
                            <ion-icon name="close-outline"></ion-icon>
                        </button>
                    </div>

                    <ul class="sidebar-menu-category-list">
                        <!-- All Products Button -->
                        <li class="sidebar-menu-category">
                            <button class="sidebar-accordion-menu" onclick="loadProducts('all')">
                                <div class="menu-title-flex">
                                    <img src="./images/category/bag.svg" alt="All" width="20" height="20" class="menu-title-img">
                                    <p class="menu-title">Show All</p>
                                </div>
                                <div>
                                    <ion-icon name="add-outline" class="add-icon"></ion-icon>
                                    <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
                                </div>
                            </button>
                        </li>

                        <?php
                        // Get distinct categories
                        $category_sql = "SELECT DISTINCT category 
                        FROM products 
                        WHERE type = 'normal'";

                        $category_result = mysqli_query($conn, $category_sql);
                        while ($cat = mysqli_fetch_assoc($category_result)):
                        ?>
                            <li class="sidebar-menu-category">
                                <button class="sidebar-accordion-menu"
                                    onclick="loadProducts('<?php echo addslashes($cat['category']); ?>')"
                                    data-accordion-btn>
                                    <div class="menu-title-flex">
                                        <img src="./images/category/bag.svg"
                                            alt="<?php echo htmlspecialchars($cat['category']); ?>"
                                            width="20" height="20"
                                            class="menu-title-img">
                                        <p class="menu-title"><?php echo htmlspecialchars($cat['category']); ?></p>
                                    </div>
                                    <div>
                                        <ion-icon name="add-outline" class="add-icon"></ion-icon>
                                        <ion-icon name="remove-outline" class="remove-icon"></ion-icon>
                                    </div>
                                </button>

                                <!-- Submenu -->
                                <ul class="sidebar-submenu-category-list" data-accordion>
                                    <?php
                                    $sub_sql = "SELECT id, name FROM products 
                                    WHERE category = '" . mysqli_real_escape_string($conn, $cat['category']) . "' 
                                    AND type = 'normal'";


                                    $sub_result = mysqli_query($conn, $sub_sql);
                                    while ($sub = mysqli_fetch_assoc($sub_result)):
                                    ?>
                                        <li class="sidebar-submenu-category">
                                            <a href="product-details.php?id=<?php echo $sub['id']; ?>" class="sidebar-submenu-title">
                                                <p class="product-name"><?php echo htmlspecialchars($sub['name']); ?></p>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>

            <!-- NEW PRODUCT -->
            <div class="product-box">
                <div class="product-box" id="product-list">
                    <?php
                    $query = "SELECT p.*, ROUND(AVG(f.rating), 1) AS avg_rating
                            FROM products p
                            LEFT JOIN reviews f ON p.id = f.product_id
                            WHERE p.type = 'normal'
                            GROUP BY p.id
                            ORDER BY p.id DESC";
                    $result = mysqli_query($conn, $query);
                    ?>

                    <div class="product-main">
                        <h2 class="title">New Products</h2>

                        <div class="product-grid">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <?php
                                // Get product images (first = default, second = hover)
                                $img_sql = "SELECT image FROM product_images WHERE product_id = {$row['id']} ORDER BY id ASC LIMIT 2";
                                $img_res = mysqli_query($conn, $img_sql);
                                $images = [];
                                while ($img = mysqli_fetch_assoc($img_res)) {
                                    $images[] = $img['image'];
                                }
                                $default_img = $images[0] ?? "no-image.png";
                                $hover_img = $images[1] ?? $default_img;
                                ?>
                                <div class="showcase">

                                    <!-- Product Images -->
                                    <div class="showcase-banner">
                                        <a href="./product-detail/index.php?id=<?= $row['id'] ?>">
                                            <img src="./images/uploads/<?= $default_img ?>"
                                                alt="<?= htmlspecialchars($row['name']) ?>"
                                                width="300" class="product-img default">

                                            <img src="./images/uploads/<?= $hover_img ?>"
                                                alt="<?= htmlspecialchars($row['name']) ?>"
                                                width="300" class="product-img hover">
                                        </a>

                                        <?php if ($row['discount'] > 0): ?>
                                            <p class="showcase-badge"><?= $row['discount'] ?>%</p>
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

                                    <!-- Product Content -->
                                    <div class="showcase-content">
                                        <a href="#" class="showcase-category"><?= htmlspecialchars($row['category']) ?></a>

                                        <a href="./product-detail/index.php?id=<?= $row['id'] ?>">
                                            <h3 class="showcase-title"><?= htmlspecialchars($row['name']) ?></h3>
                                        </a>

                                        <!-- Rating -->
                                        <div class="showcase-rating">
                                            <?php
                                            $rating = $row['avg_rating'] ?? 0;
                                            for ($i = 1; $i <= 5; $i++):
                                            ?>
                                                <ion-icon name="<?= $i <= $rating ? 'star' : 'star-outline' ?>"></ion-icon>
                                            <?php endfor; ?>
                                        </div>

                                        <!-- Price -->
                                        <div class="price-box">
                                            <?php
                                            if ($row['discount'] > 0) {
                                                $final_price = $row['price'] - ($row['price'] * $row['discount'] / 100);
                                            ?>
                                                <p class="price">PKR <?= number_format($final_price) ?></p>
                                                <del>PKR <?= number_format($row['price']) ?></del>
                                            <?php } else { ?>
                                                <p class="price">PKR <?= number_format($row['price']) ?></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- PRODUCT FEATURED -->
                <div class="product-featured" data-aos="zoom-in" data-aos-duration="400" data-aos-delay="100">
                    <h2 class="title" style="text-align: center;">Deal of the day</h2>
                    <div class="showcase-wrapper has-scrollbar">

                        <?php
                        $result = mysqli_query($conn, "SELECT * FROM deals WHERE status='active' ORDER BY created_at DESC");
                        while ($deal = mysqli_fetch_assoc($result)) {
                        ?>
                            <div class="showcase-container">
                                <div class="showcase">
                                    <div class="showcase-banner">
                                        <img src="images/deals/<?= $deal['image'] ?>"
                                            alt="<?= htmlspecialchars($deal['title']) ?>"
                                            class="showcase-img">
                                    </div>

                                    <div class="showcase-content">
                                        <div class="showcase-rating">
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star"></ion-icon>
                                            <ion-icon name="star-outline"></ion-icon>
                                            <ion-icon name="star-outline"></ion-icon>
                                        </div>

                                        <h3 class="showcase-title">
                                            <a href="#" class="showcase-title"><?= htmlspecialchars($deal['title']) ?></a>
                                        </h3>

                                        <p class="showcase-desc"><?= htmlspecialchars($deal['description']) ?></p>

                                        <div class="price-box">
                                            <p class="price"><?= number_format($deal['price']) ?></p>
                                            <del><?= number_format($deal['old_price']) ?></del>
                                        </div>

                                        <button class="add-cart-btn">Soon</button>

                                        <div class="showcase-status">
                                            <div class="wrapper">
                                                <p>already sold: <b><?= (int)$deal['sold'] ?></b></p>
                                                <p>available: <b><?= (int)$deal['available'] ?></b></p>
                                            </div>
                                            <div class="showcase-status-bar"></div>
                                        </div>

                                        <div class="countdown-box">
                                            <p class="countdown-desc">Hurry Up! Offer ends in:</p>
                                            <div class="countdown" data-end="<?= $deal['end_date'] ?>">
                                                <div class="countdown-content">
                                                    <p class="display-number">0</p>
                                                    <p class="display-text">Days</p>
                                                </div>
                                                <div class="countdown-content">
                                                    <p class="display-number">0</p>
                                                    <p class="display-text">Hours</p>
                                                </div>
                                                <div class="countdown-content">
                                                    <p class="display-number">0</p>
                                                    <p class="display-text">Min</p>
                                                </div>
                                                <div class="countdown-content">
                                                    <p class="display-number">0</p>
                                                    <p class="display-text">Sec</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Product Featured Countdown -->
<script>
    document.querySelectorAll('.countdown').forEach(function(cd) {
        let endTime = new Date(cd.dataset.end).getTime();

        function updateCountdown() {
            let now = new Date().getTime();
            let diff = endTime - now;
            if (diff < 0) diff = 0;

            let days = Math.floor(diff / (1000 * 60 * 60 * 24));
            let hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((diff % (1000 * 60)) / 1000);

            let numbers = cd.querySelectorAll('.display-number');
            numbers[0].textContent = days;
            numbers[1].textContent = hours;
            numbers[2].textContent = minutes;
            numbers[3].textContent = seconds;
        }
        setInterval(updateCountdown, 1000);
        updateCountdown();
    });
</script>