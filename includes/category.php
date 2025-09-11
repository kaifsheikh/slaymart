<?php
// Try to include the database configuration with different possible paths
$db_paths = [
    '../config/db.php',
];
$db_included = false;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        include $path;
        $db_included = true;
        break;
    }
}
// Fetch distinct categories from the products table
$categories_query = "SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != '' ORDER BY category ASC";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result->num_rows > 0) {
    while($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
// Function to get product count for each category
function getProductCount($conn, $category_name) {
    $category_name = $conn->real_escape_string($category_name);
    $count_query = "SELECT COUNT(*) as count FROM products WHERE category = '$category_name'";
    $count_result = $conn->query($count_query);
    $count_data = $count_result->fetch_assoc();
    return $count_data['count'];
}
?>
<!-- Compact Category Section -->
<div id="category-navigation-wrapper">
    <!-- Include Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Scoped styles - only affect elements within the wrapper */
        #category-navigation-wrapper * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        #category-navigation-wrapper {
            font-family: 'Poppins', sans-serif;
            color: #334155;
            line-height: 1.6;
            padding: 20px;
        }
        
        #category-navigation-wrapper .compact-category-section {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 25px;
        }
        
        #category-navigation-wrapper .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        #category-navigation-wrapper .category-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
        }
        
        #category-navigation-wrapper .nav-buttons {
            display: flex;
            gap: 10px;
        }
        
        #category-navigation-wrapper .nav-btn {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        #category-navigation-wrapper .nav-btn:hover {
            background: #6366f1;
            color: white;
            border-color: #6366f1;
        }
        
        #category-navigation-wrapper .category-item-container {
            display: flex;
            gap: 15px;
            padding: 10px 5px;
            overflow-x: auto;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE and Edge */
            scroll-behavior: smooth;
        }
        
        #category-navigation-wrapper .category-item-container::-webkit-scrollbar {
            display: none; /* Chrome, Safari and Opera */
        }
        
        #category-navigation-wrapper .category-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            min-width: 160px;
            flex-shrink: 0;
            border: 1px solid #e2e8f0;
        }
        
        #category-navigation-wrapper .category-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        #category-navigation-wrapper .category-img-box {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 90px;
            background: linear-gradient(135deg, #f0f4ff, #e0e7ff);
            padding: 15px;
        }
        
        #category-navigation-wrapper .category-img-box img {
            width: 40px;
            height: 40px;
            object-fit: contain;
        }
        
        #category-navigation-wrapper .category-content-box {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        
        #category-navigation-wrapper .category-content-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        #category-navigation-wrapper .category-item-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
        }
        
        #category-navigation-wrapper .category-item-amount {
            font-size: 0.75rem;
            color: #64748b;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 12px;
        }
        
        #category-navigation-wrapper .category-btn {
            display: inline-block;
            margin-top: auto;
            padding: 6px 12px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        #category-navigation-wrapper .category-btn:hover {
            opacity: 0.9;
            box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            #category-navigation-wrapper .compact-category-section {
                padding: 20px;
                margin: 30px auto;
            }
            
            #category-navigation-wrapper .category-item {
                min-width: 140px;
            }
            
            #category-navigation-wrapper .category-title {
                font-size: 1.3rem;
            }
            
            #category-navigation-wrapper .category-img-box {
                height: 85px;
            }
        }
        
        @media (max-width: 576px) {
            #category-navigation-wrapper .compact-category-section {
                padding: 15px;
                margin: 20px auto;
            }
            
            #category-navigation-wrapper .category-item {
                min-width: 130px;
            }
            
            #category-navigation-wrapper .category-title {
                font-size: 1.2rem;
            }
            
            #category-navigation-wrapper .category-img-box {
                height: 80px;
                padding: 12px;
            }
            
            #category-navigation-wrapper .category-content-box {
                padding: 12px;
            }
            
            #category-navigation-wrapper .nav-btn {
                width: 32px;
                height: 32px;
            }
        }
    </style>
    
    <section class="compact-category-section">
        <div class="category-header">
            <h2 class="category-title">Browse Categories</h2>
            <div class="nav-buttons">
                <div class="nav-btn prev-btn">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="nav-btn next-btn">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
        
        <div class="category-item-container">
            <!-- Static Category Show All Product -->
            <div class="category-item">
                <div class="category-img-box">
                    <img src="https://cdn-icons-png.flaticon.com/512/891/891462.png" alt="All Products">
                </div>
                <div class="category-content-box">
                    <div class="category-content-flex">
                        <h3 class="category-item-title">All Products</h3>
                        <p class="category-item-amount">(All)</p>
                    </div>
                    <a href="javascript:void(0);" class="category-btn" onclick="loadProducts('all')">Show all</a>
                </div>
            </div>
            
            <!-- PHP Dynamic Categories -->
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <?php 
                        // Get product count for this category
                        $product_count = getProductCount($conn, $category);
                        
                        // Determine icon based on category name
                        $icon = '';
                        $category_lower = strtolower($category);
                        
                        if (strpos($category_lower, 'electronic') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/3081/3081559.png';
                        } elseif (strpos($category_lower, 'fashion') !== false || strpos($category_lower, 'cloth') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/3144/3144456.png';
                        } elseif (strpos($category_lower, 'home') !== false || strpos($category_lower, 'kitchen') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/2838/2838694.png';
                        } elseif (strpos($category_lower, 'book') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/857/857455.png';
                        } elseif (strpos($category_lower, 'sport') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/869/869548.png';
                        } elseif (strpos($category_lower, 'beauty') !== false || strpos($category_lower, 'cosmetic') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/3448/3448632.png';
                        } elseif (strpos($category_lower, 'toy') !== false || strpos($category_lower, 'game') !== false) {
                            $icon = 'https://cdn-icons-png.flaticon.com/512/2454/2454273.png';
                        } else {
                            // Default icon
                            $icon = 'https://cdn-icons-png.flaticon.com/512/25/25694.png';
                        }
                    ?>
                    <div class="category-item">
                        <div class="category-img-box">
                            <img src="<?= htmlspecialchars($icon) ?>" alt="<?= htmlspecialchars($category) ?>">
                        </div>
                        <div class="category-content-box">
                            <div class="category-content-flex">
                                <h3 class="category-item-title"><?= htmlspecialchars($category) ?></h3>
                                <p class="category-item-amount">(<?= $product_count ?>)</p>
                            </div>
                            <a href="javascript:void(0);" class="category-btn" onclick="loadProducts('<?= htmlspecialchars($category) ?>')">Show all</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="padding: 20px; text-align: center; color: #64748b;">No categories found in the database.</p>
            <?php endif; ?>
        </div>
    </section>
    
    <script>
        // Function to simulate loading products
        function loadProducts(category) {
            // In a real implementation, this would fetch products from a server
            // and update the product listing area
            
            // Redirect to products page with category filter
            if (category === 'all') {
                window.location.href = 'products.php';
            } else {
                window.location.href = 'products.php?category=' + encodeURIComponent(category);
            }
        }
        
        // Navigation functionality
        const categoryContainer = document.querySelector('#category-navigation-wrapper .category-item-container');
        const nextBtn = document.querySelector('#category-navigation-wrapper .next-btn');
        const prevBtn = document.querySelector('#category-navigation-wrapper .prev-btn');
        
        nextBtn.addEventListener('click', () => {
            categoryContainer.scrollBy({ left: 200, behavior: 'smooth' });
        });
        
        prevBtn.addEventListener('click', () => {
            categoryContainer.scrollBy({ left: -200, behavior: 'smooth' });
        });
        
        // Hide navigation buttons when at the ends
        const checkScroll = () => {
            const scrollLeft = categoryContainer.scrollLeft;
            const scrollWidth = categoryContainer.scrollWidth;
            const clientWidth = categoryContainer.clientWidth;
            
            prevBtn.style.opacity = scrollLeft > 0 ? '1' : '0.5';
            prevBtn.style.cursor = scrollLeft > 0 ? 'pointer' : 'default';
            
            nextBtn.style.opacity = scrollLeft < (scrollWidth - clientWidth - 10) ? '1' : '0.5';
            nextBtn.style.cursor = scrollLeft < (scrollWidth - clientWidth - 10) ? 'pointer' : 'default';
        };
        
        categoryContainer.addEventListener('scroll', checkScroll);
        window.addEventListener('resize', checkScroll);
        
        // Initial check
        checkScroll();
    </script>
</div>