<?php
include "../../config/db.php";
include "../includes/session_check.php";
// ---- Get Product ----
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
if ($id <= 0) die("❌ Product ID missing.");
$res = mysqli_query($conn, "SELECT * FROM products WHERE id = $id LIMIT 1");
if (!$res || mysqli_num_rows($res) === 0) die("❌ Product not found.");
$product = mysqli_fetch_assoc($res);
// ---- Get Product Images ----
function getProductImages($conn, $id) {
    $images = [];
    $res = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $id ORDER BY id ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $images[] = $row;
    }
    return $images;
}
$product_images = getProductImages($conn, $id);
$success = "";
$error = "";
// ---- Update Logic ----
if (isset($_POST['update'])) {
    $name     = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $desc     = mysqli_real_escape_string($conn, $_POST['description']);
    $price    = floatval($_POST['price']);
    $discount = floatval($_POST['discount']);
    $update = "UPDATE products SET 
                name='$name', category='$category', description='$desc',
                price=$price, discount=$discount WHERE id=$id";
    if (!mysqli_query($conn, $update)) {
        $error = "Database error: " . mysqli_error($conn);
    } else {
        // ---- Upload new images ----
        $uploadDir = dirname(__DIR__,2) . "/images/uploads/";
        $allowedExt = ['jpg','jpeg','png','webp'];
        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0775, true);
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $fileName) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (in_array($ext, $allowedExt)) {
                        $newName = uniqid("p_", true) . "." . $ext;
                        if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadDir . $newName)) {
                            mysqli_query($conn, "INSERT INTO product_images (product_id,image) VALUES ($id,'$newName')");
                        }
                    }
                }
            }
        }
        $success = "✅ Product updated successfully!";
        $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$id LIMIT 1"));
        $product_images = getProductImages($conn, $id);
    }
}
// ---- Delete Single Image ----
if (isset($_GET['delete_image'])) {
    $imgId = intval($_GET['delete_image']);
    $imgRes = mysqli_query($conn, "SELECT * FROM product_images WHERE id=$imgId AND product_id=$id LIMIT 1");
    if ($imgRes && mysqli_num_rows($imgRes) > 0) {
        $imgRow = mysqli_fetch_assoc($imgRes);
        $uploadDir = dirname(__DIR__,2) . "/images/uploads/";
        if (file_exists($uploadDir . $imgRow['image'])) @unlink($uploadDir . $imgRow['image']);
        mysqli_query($conn, "DELETE FROM product_images WHERE id=$imgId");
        header("Location: product_update.php?id=$id&msg=img_deleted");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
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
            background-color: #f8f9fe;
            color: var(--dark);
            min-height: 100vh;
            padding-bottom: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .page-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }
        
        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px rgba(50, 50, 93, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h4 {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            height: auto;
            background-color: white;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
            background-color: white;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(94, 114, 228, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(94, 114, 228, 0.4);
            background: linear-gradient(135deg, #4c63d2, #7549d9);
        }
        
        .btn-outline-secondary {
            background-color: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--light-gray);
            color: var(--dark);
            transform: translateY(-3px);
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(45, 206, 137, 0.1);
            color: #0a5c3c;
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(245, 54, 92, 0.1);
            color: #a02639;
            border-left: 4px solid var(--danger);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .input-icon .form-control,
        .input-icon .form-select {
            padding-left: 45px;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-upload-label {
            display: block;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
        }
        
        .file-upload:hover .file-upload-label {
            border-color: var(--primary);
            background-color: rgba(94, 114, 228, 0.05);
        }
        
        .file-upload-label i {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .current-image {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .current-image img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            margin-right: 15px;
            transition: var(--transition);
        }
        
        .current-image img:hover {
            transform: scale(1.05);
        }
        
        .current-image-info {
            flex: 1;
        }
        
        .current-image-info h6 {
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .current-image-info p {
            margin-bottom: 0;
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 15px;
        }
        
        .preview-box {
            width: 120px;
            height: 120px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: var(--light-gray);
            box-shadow: 0 2px 4px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            position: relative;
        }
        
        .preview-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .preview-placeholder {
            color: var(--gray);
            text-align: center;
            padding: 10px;
        }
        
        .preview-placeholder i {
            font-size: 2rem;
            margin-bottom: 5px;
            color: var(--primary);
        }
        
        .preview-placeholder p {
            font-size: 0.75rem;
            margin: 0;
        }
        
        .product-preview {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary);
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.05);
        }
        
        .product-preview img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }
        
        .product-preview-info h5 {
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .product-preview-info p {
            margin-bottom: 0;
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .price-container {
            display: flex;
            flex-direction: column;
            margin-top: 5px;
        }
        
        .original-price {
            font-weight: 500;
            color: var(--gray);
            text-decoration: line-through;
            font-size: 0.9rem;
        }
        
        .discounted-price {
            font-weight: 700;
            color: var(--success);
            font-size: 1.1rem;
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .image-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
        }
        
        .image-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(50, 50, 93, 0.15), 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        
        .image-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            display: block;
        }
        
        .image-item .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--transition);
        }
        
        .image-item:hover .delete-btn {
            opacity: 1;
        }
        
        .image-item .delete-btn:hover {
            background-color: #ec0c38;
            transform: scale(1.1);
        }
        
        .no-images {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            background-color: var(--light-gray);
            border-radius: var(--border-radius);
            margin-top: 20px;
        }
        
        .no-images i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary);
            opacity: 0.7;
        }
        
        .badge-id {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-size: 0.8rem;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .form-col {
            flex: 1;
            padding: 0 10px;
            min-width: 250px;
        }
        
        .form-footer {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--light-gray);
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .page-header {
                padding: 30px 0;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .product-preview {
                flex-direction: column;
                text-align: center;
            }
            
            .product-preview img {
                margin-right: 0;
                margin-bottom: 15px;
            }
            
            .preview-container {
                justify-content: center;
            }
            
            .preview-box {
                width: 100px;
                height: 100px;
            }
            
            .form-footer {
                flex-direction: column;
            }
            
            .btn-primary, .btn-outline-secondary {
                width: 100%;
            }
            
            .image-gallery {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 15px;
            }
        }
        
        /* Animation for page load */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .form-container {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-edit me-2"></i> Update Product</h1>
                    <p class="mb-0">Edit product information and images</p>
                </div>
                <a href="manage_product.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Products
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Product Preview -->
        <div class="product-preview">
            <?php 
            $firstImage = !empty($product_images) ? "../../images/uploads/" . $product_images[0]['image'] : "https://via.placeholder.com/100";
            // Calculate discounted price
            $originalPrice = floatval($product['price']);
            $discount = floatval($product['discount']);
            $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);
            ?>
            <img src="<?= $firstImage ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div class="product-preview-info">
                <h5><?= htmlspecialchars($product['name']) ?></h5>
                <p><?= htmlspecialchars($product['category']) ?></p>
                <div class="price-container">
                    <?php if ($discount > 0): ?>
                        <span class="original-price">Rs. <?= number_format($originalPrice, 2) ?></span>
                        <span class="discounted-price">Rs. <?= number_format($discountedPrice, 2) ?></span>
                    <?php else: ?>
                        <span class="discounted-price">Rs. <?= number_format($originalPrice, 2) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Update Form -->
        <div class="form-container">
            <div class="card-header">
                <h4><i class="fas fa-box me-2"></i> Product Information</h4>
                <span class="badge-id">ID: <?= $product['id'] ?></span>
            </div>
            
            <div class="card-body">
                <!-- Success Message -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        <div><?= $success ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?= $error ?></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Product Form -->
                <form method="post" enctype="multipart/form-data" id="productForm">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Product Name</label>
                                <div class="input-icon">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" placeholder="Enter product name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <div class="input-icon">
                                    <i class="fas fa-folder"></i>
                                    <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($product['category']) ?>" placeholder="Enter category" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <div class="input-icon">
                            <i class="fas fa-align-left"></i>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter product description"><?= htmlspecialchars($product['description']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="section-title mt-4">
                        <i class="fas fa-dollar-sign"></i> Pricing Information
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Price (Rs.)</label>
                                <div class="input-icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <input type="number" step="0.01" name="price" class="form-control" value="<?= $product['price'] ?>" placeholder="Enter price" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Discount (%)</label>
                                <div class="input-icon">
                                    <i class="fas fa-percentage"></i>
                                    <input type="number" step="0.01" name="discount" class="form-control" value="<?= $product['discount'] ?>" placeholder="Enter discount" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-title mt-4">
                        <i class="fas fa-images"></i> Product Images
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Upload New Images (Multiple allowed)</label>
                        <div class="file-upload">
                            <input type="file" name="images[]" id="images" multiple>
                            <label for="images" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose images or drag them here</span>
                            </label>
                        </div>
                        <small class="text-muted">You can select multiple images (jpg, jpeg, png, webp)</small>
                        <div class="preview-container" id="preview-container"></div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" name="update" class="btn-primary">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                        <a href="manage_product.php" class="btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Current Images Section -->
        <div class="form-container mt-4">
            <div class="card-header">
                <h4><i class="fas fa-images me-2"></i> Current Product Images</h4>
            </div>
            
            <div class="card-body">
                <?php if (!empty($product_images)): ?>
                    <div class="image-gallery">
                        <?php foreach ($product_images as $img): ?>
                            <div class="image-item">
                                <img src="../../images/uploads/<?= $img['image'] ?>" alt="Product Image">
                                <a href="product_update.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>" class="delete-btn" title="Delete Image">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-images">
                        <i class="fas fa-images"></i>
                        <h5>No Images Available</h5>
                        <p>This product doesn't have any images yet. Upload some using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Multiple file upload preview
        document.getElementById('images').addEventListener('change', function() {
            const files = this.files;
            const previewContainer = document.getElementById('preview-container');
            previewContainer.innerHTML = '';
            
            if (files.length > 0) {
                [...files].forEach(file => {
                    if (file.type.match('image.*')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const previewBox = document.createElement('div');
                            previewBox.className = 'preview-box';
                            
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.alt = 'Preview';
                            
                            previewBox.appendChild(img);
                            previewContainer.appendChild(previewBox);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                const placeholder = document.createElement('div');
                placeholder.className = 'preview-box';
                placeholder.innerHTML = `
                    <div class="preview-placeholder">
                        <i class="fas fa-image"></i>
                        <p>No images selected</p>
                    </div>
                `;
                previewContainer.appendChild(placeholder);
            }
        });
        
        // Initialize with placeholder
        document.addEventListener('DOMContentLoaded', function() {
            const previewContainer = document.getElementById('preview-container');
            const placeholder = document.createElement('div');
            placeholder.className = 'preview-box';
            placeholder.innerHTML = `
                <div class="preview-placeholder">
                    <i class="fas fa-image"></i>
                    <p>No images selected</p>
                </div>
            `;
            previewContainer.appendChild(placeholder);
        });
    </script>
</body>
</html>