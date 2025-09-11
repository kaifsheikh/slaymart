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
<div class="premium-products">
    <div class="section-header">
        <h2 class="section-title">🔥 Premium Exclusive Products</h2>
        <div class="title-underline"></div>
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
                        <a href="./exclusive-detail.php?id=<?= $row['id'] ?>" class="action-btn view-btn" title="Quick View">
                            <ion-icon name="eye-outline"></ion-icon>
                        </a>
                        <a href="../add-to-cart/index.php?add=<?= $row['id'] ?>" class="action-btn cart-btn" title="Add to Cart">
                            <ion-icon name="cart-outline"></ion-icon>
                        </a>
                        <a href="../checkout/buy_now.php?id=<?= $row['id'] ?>" class="action-btn buy-btn" title="Buy Now">
                            <ion-icon name="flash-outline"></ion-icon>
                        </a>
                    </div>
                </div>
                
                <!-- Product Content -->
                <div class="product-content">
                    <div class="product-category"><?= htmlspecialchars($row['category']) ?></div>
                    <h3 class="product-title"><?= htmlspecialchars($row['name']) ?></h3>
                    
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
                            <?= strtoupper($row['stock_status']) ?>
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
                        <a href="../add-to-cart/index.php?add=<?= $row['id'] ?>" class="btn add-to-cart">
                            <ion-icon name="cart-outline"></ion-icon> Add to Cart
                        </a>
                        <a href="../checkout/buy_now.php?id=<?= $row['id'] ?>" class="btn buy-now">
                            <ion-icon name="flash-outline"></ion-icon> Buy Now
                        </a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<style>
/* Premium Products Section */
.premium-products {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
    position: relative;
}

.section-title {
    font-size: 2.8rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 15px;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.title-underline {
    width: 120px;
    height: 5px;
    background: linear-gradient(90deg, #FFD700, #FFA500);
    margin: 0 auto;
    border-radius: 3px;
    box-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

/* Product Card */
.product-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    transform-origin: center bottom;
}

.product-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
}

/* Product Badge */
.product-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #FF6B6B, #FF4757);
    color: white;
    font-weight: 700;
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 0.85rem;
    z-index: 10;
    box-shadow: 0 5px 15px rgba(255, 71, 87, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Product Image */
.product-image-container {
    position: relative;
    overflow: hidden;
    height: 300px;
    background: #f8f9fa;
    border-radius: 20px 20px 0 0;
}

.image-link {
    display: block;
    height: 100%;
}

.product-img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.7s ease, transform 0.7s ease;
}

.product-img.hover {
    opacity: 0;
}

.product-image-container:hover .product-img.default {
    opacity: 0;
    transform: scale(1.05);
}

.product-image-container:hover .product-img.hover {
    opacity: 1;
    transform: scale(1.05);
}

/* Quick Actions */
.quick-actions {
    position: absolute;
    bottom: 20px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    gap: 15px;
    opacity: 0;
    transition: opacity 0.4s ease, transform 0.4s ease;
    transform: translateY(20px);
}

.product-image-container:hover .quick-actions {
    opacity: 1;
    transform: translateY(0);
}

.action-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.action-btn.view-btn:hover {
    background: #3498db;
    color: white;
}

.action-btn.cart-btn:hover {
    background: #2ecc71;
    color: white;
}

.action-btn.buy-btn:hover {
    background: #e74c3c;
    color: white;
}

.action-btn ion-icon {
    font-size: 1.3rem;
    color: #333;
    transition: color 0.3s ease;
}

.action-btn:hover ion-icon {
    color: white;
}

/* Product Content */
.product-content {
    padding: 25px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 300px);
}

.product-category {
    color: #7f8c8d;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 10px;
    font-weight: 600;
}

.product-title {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #2c3e50;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    height: 3.6rem;
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 15px;
}

.product-rating ion-icon {
    color: #f39c12;
    font-size: 1.1rem;
}

.rating-value {
    font-weight: 600;
    color: #7f8c8d;
    margin-left: 5px;
}

.product-description {
    color: #7f8c8d;
    font-size: 0.95rem;
    line-height: 1.5;
    margin-bottom: 20px;
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
    gap: 15px;
    margin-bottom: 20px;
}

.color-options {
    display: flex;
    gap: 8px;
}

.color-dot {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #eee;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.color-dot:hover {
    transform: scale(1.2);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.size-options {
    display: flex;
    gap: 8px;
}

.size-badge {
    background: #f8f9fa;
    color: #7f8c8d;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 1px solid #eee;
}

.size-badge:hover {
    background: #3498db;
    color: white;
    border-color: #3498db;
}

/* Product Footer */
.product-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
    margin-bottom: 20px;
}

.stock-status {
    display: flex;
    align-items: center;
    font-weight: 600;
    font-size: 0.85rem;
}

.status-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 8px;
    box-shadow: 0 0 0 2px rgba(46, 204, 113, 0.3);
}

.in-stock {
    color: #27ae60;
}

.in-stock .status-dot {
    background: #27ae60;
    box-shadow: 0 0 0 2px rgba(39, 174, 96, 0.3);
}

.out-stock {
    color: #e74c3c;
}

.out-stock .status-dot {
    background: #e74c3c;
    box-shadow: 0 0 0 2px rgba(231, 76, 60, 0.3);
}

.price-container {
    text-align: right;
}

.current-price, .discount-price {
    font-weight: 700;
    font-size: 1.3rem;
    color: #2c3e50;
}

.discount-price {
    color: #e74c3c;
}

.original-price {
    font-size: 0.9rem;
    color: #95a5a6;
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
    padding: 12px 15px;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn.add-to-cart {
    background: linear-gradient(135deg, #3498db, #2980b9);
    color: white;
}

.btn.add-to-cart:hover {
    background: linear-gradient(135deg, #2980b9, #21618c);
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(41, 128, 185, 0.4);
}

.btn.buy-now {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.btn.buy-now:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(192, 57, 43, 0.4);
}

.btn ion-icon {
    font-size: 1.1rem;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
    }
}

@media (max-width: 992px) {
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .section-title {
        font-size: 2.4rem;
    }
    
    .product-image-container {
        height: 260px;
    }
    
    .product-content {
        height: calc(100% - 260px);
    }
}

@media (max-width: 768px) {
    .premium-products {
        padding: 30px 15px;
        border-radius: 15px;
    }
    
    .product-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 20px;
    }
    
    .product-image-container {
        height: 240px;
    }
    
    .product-content {
        height: calc(100% - 240px);
        padding: 20px;
    }
    
    .section-title {
        font-size: 2.2rem;
    }
    
    .product-title {
        font-size: 1.2rem;
        height: 3.2rem;
    }
    
    .btn {
        padding: 10px 12px;
        font-size: 0.8rem;
    }
    
    .btn ion-icon {
        font-size: 1rem;
    }
}

@media (max-width: 576px) {
    .premium-products {
        padding: 20px 10px;
    }
    
    .product-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }
    
    .product-image-container {
        height: 280px;
    }
    
    .product-content {
        height: auto;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .product-options {
        flex-direction: column;
        gap: 10px;
    }
    
    .product-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
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