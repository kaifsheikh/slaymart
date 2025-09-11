<?php
include '../config/db.php';
// ✅ Enhanced search function with multiple words support
function searchProducts($conn, $searchTerm) {
    $searchTerm = strtolower($searchTerm);
    $searchWords = explode(' ', trim($searchTerm));
    
    $whereConditions = [];
    $params = [];
    $types = '';
    
    foreach ($searchWords as $word) {
        if (!empty($word)) {
            $whereConditions[] = "(LOWER(p.name) LIKE ? OR LOWER(p.category) LIKE ? OR LOWER(p.description) LIKE ?)";
            $searchParam = "%{$word}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= 'sss';
        }
    }
    
    if (empty($whereConditions)) {
        return null;
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // ✅ Fetch products with first image
    $sql = "SELECT p.*, 
                   (SELECT pi.image FROM product_images pi WHERE pi.product_id = p.id ORDER BY pi.id ASC LIMIT 1) AS product_image
            FROM products p
            WHERE {$whereClause}";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Search</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #5e72e4;
            --secondary: #825ee4;
            --info: #11cdef;
            --success: #2dce89;
            --warning: #fb6340;
            --danger: #f5365c;
            --light: #f7fafc;
            --dark: #32325d;
            --gray: #8898aa;
            --light-gray: #f4f5f7;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            min-height: 100vh;
            padding: 20px;
            color: var(--dark);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
            animation: fadeIn 0.8s ease forwards;
        }
        
        .header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .search-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease 0.2s forwards;
            opacity: 0;
        }
        
        .search-form {
            display: flex;
            position: relative;
            margin-bottom: 20px;
        }
        
        .search-input {
            flex: 1;
            padding: 18px 25px;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            background: var(--light-gray);
            outline: none;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05);
        }
        
        .search-input:focus {
            background: white;
            box-shadow: 0 0 0 4px rgba(94, 114, 228, 0.2);
        }
        
        .search-button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            box-shadow: 0 4px 6px rgba(94, 114, 228, 0.2);
        }
        
        .search-button:hover {
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 8px 15px rgba(94, 114, 228, 0.3);
        }
        
        .search-button i {
            font-size: 1.3rem;
        }
        
        .search-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        
        .search-tag {
            background: var(--light-gray);
            color: var(--dark);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .search-tag:hover {
            background: rgba(94, 114, 228, 0.1);
            color: var(--primary);
        }
        
        .search-tag i {
            font-size: 0.8rem;
        }
        
        .results-container {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            animation: fadeIn 0.8s ease 0.4s forwards;
            opacity: 0;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .results-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .results-title i {
            color: var(--primary);
        }
        
        .results-count {
            font-size: 1rem;
            color: var(--gray);
            background: var(--light-gray);
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }
        
        .product-card {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            background: white;
            cursor: pointer;
            position: relative;
            text-decoration: none;
            display: flex;
            flex-direction: column;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        
        .product-image-container {
            position: relative;
            overflow: hidden;
            height: 220px;
        }
        
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .product-card:hover .product-image {
            transform: scale(1.08);
        }
        
        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--danger);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 1;
            box-shadow: 0 4px 8px rgba(245, 54, 92, 0.3);
        }
        
        .product-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-category {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .product-category i {
            font-size: 0.8rem;
        }
        
        .product-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--dark);
            line-height: 1.3;
        }
        
        .product-description {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: 20px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }
        
        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .product-price-container {
            display: flex;
            flex-direction: column;
        }
        
        .product-original-price {
            font-size: 0.9rem;
            color: var(--gray);
            text-decoration: line-through;
            margin-bottom: 5px;
        }
        
        .product-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--success);
        }
        
        .product-rating {
            display: flex;
            align-items: center;
            color: #f39c12;
        }
        
        .product-rating i {
            margin-right: 4px;
            font-size: 0.9rem;
        }
        
        .product-rating span {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--gray);
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: var(--gray);
        }
        
        .no-results i {
            font-size: 4.5rem;
            margin-bottom: 20px;
            color: var(--primary);
            opacity: 0.7;
        }
        
        .no-results h3 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .no-results p {
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid var(--light-gray);
            border-top: 5px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading p {
            color: var(--gray);
            font-size: 1.2rem;
            font-weight: 500;
        }
        
        /* Remove text underlines */
        a {
            text-decoration: none;
        }
        
        /* Animation for page load */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 25px;
            }
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }
            
            .header p {
                font-size: 1.1rem;
            }
            
            .search-container,
            .results-container {
                padding: 20px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }
            
            .results-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .product-image-container {
                height: 180px;
            }
            
            .product-content {
                padding: 15px;
            }
            
            .product-name {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                margin-bottom: 30px;
            }
            
            .search-input {
                padding: 15px 20px;
                font-size: 1rem;
            }
            
            .search-button {
                width: 45px;
                height: 45px;
            }
            
            .search-button i {
                font-size: 1.2rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .product-card {
                max-width: 350px;
                margin: 0 auto;
            }
        }
    </style>
    <link rel="shortcut icon" href="../images/logo/favicon.png" type="image/x-icon">
</head>
<body>
<div class="container">
  <div class="header">
    <h1><i class="fas fa-search me-3"></i>Product Search</h1>
    <p>Find your favorite products with our intelligent search system</p>
  </div>
  
  <div class="search-container">
    <form class="search-form" method="GET" action="">
      <input type="text" class="search-input" name="search" placeholder="Search for products or categories..." 
             value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
      <button type="submit" class="search-button">
        <i class="fas fa-search"></i>
      </button>
    </form>
    
    <div class="search-tags">
      <div class="search-tag" onclick="document.querySelector('.search-input').value='Electronics'; document.querySelector('.search-form').submit();">
        <i class="fas fa-laptop"></i> Electronics
      </div>
      <div class="search-tag" onclick="document.querySelector('.search-input').value='Clothing'; document.querySelector('.search-form').submit();">
        <i class="fas fa-tshirt"></i> Clothing
      </div>
      <div class="search-tag" onclick="document.querySelector('.search-input').value='Home'; document.querySelector('.search-form').submit();">
        <i class="fas fa-home"></i> Home
      </div>
      <div class="search-tag" onclick="document.querySelector('.search-input').value='Beauty'; document.querySelector('.search-form').submit();">
        <i class="fas fa-spa"></i> Beauty
      </div>
    </div>
  </div>
  
  <div class="results-container">
    <?php
    if(isset($_GET['search']) && !empty($_GET['search'])){
        $search = trim($_GET['search']);
        $result = searchProducts($conn, $search);
        
        if($result && $result->num_rows > 0){
            echo "<div class='results-header'>";
            echo "<h2 class='results-title'><i class='fas fa-box-open me-2'></i>Search Results</h2>";
            echo "<div class='results-count'><i class='fas fa-filter me-2'></i>{$result->num_rows} products found</div>";
            echo "</div>";
            
            echo "<div class='products-grid'>";
            while($row = $result->fetch_assoc()){
                $originalPrice = $row['price'];
                $discount = $row['discount'] ?? 0;
                $discountedPrice = $discount > 0 ? $originalPrice - ($originalPrice * ($discount/100)) : $originalPrice;
                
                $rating = number_format(mt_rand(35, 50) / 10, 1); // demo random rating
                $productImage = !empty($row['product_image']) ? "../images/uploads/{$row['product_image']}" : "https://picsum.photos/seed/".rand()."/600/400.jpg";
                
                echo "<a href='../product-detail/index.php?id={$row['id']}' class='product-card'>";
                if ($discount > 0) {
                    echo "<div class='product-badge'>{$discount}% OFF</div>";
                }
                echo "<div class='product-image-container'>";
                echo "<img src='".htmlspecialchars($productImage)."' class='product-image' alt='".htmlspecialchars($row['name'])."'>";
                echo "</div>";
                echo "<div class='product-content'>";
                echo "<div class='product-category'><i class='fas fa-tag me-1'></i>".htmlspecialchars($row['category'])."</div>";
                echo "<h3 class='product-name'>".htmlspecialchars($row['name'])."</h3>";
                echo "<p class='product-description'>".htmlspecialchars($row['description'])."</p>";
                echo "<div class='product-footer'>";
                echo "<div class='product-price-container'>";
                if ($discount > 0) {
                    echo "<div class='product-original-price'>Rs. ".number_format($originalPrice, 2)."</div>";
                }
                echo "<div class='product-price'>Rs. ".number_format($discountedPrice, 2)."</div>";
                echo "</div>";
                echo "<div class='product-rating'><i class='fas fa-star'></i> <span>{$rating}</span></div>";
                echo "</div></div></a>";
            }
            echo "</div>";
        } else {
            echo "<div class='no-results'><i class='fas fa-search'></i><h3>No Products Found</h3>
                  <p>We couldn't find any products matching \"{$search}\". Please try a different search term or browse our categories.</p></div>";
        }
    } else {
        echo "<div class='no-results'><i class='fas fa-search'></i><h3>Search for Products</h3>
              <p>Enter a product name or category in the search box above to find what you're looking for.</p></div>";
    }
    ?>
  </div>
</div>
<script>
  document.querySelector('.search-form').addEventListener('submit', function() {
      const resultsContainer = document.querySelector('.results-container');
      resultsContainer.innerHTML = `
          <div class="loading">
              <div class="spinner"></div>
              <p>Searching for products...</p>
          </div>
      `;
  });
</script>
</body>
</html>