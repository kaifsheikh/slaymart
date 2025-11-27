<?php
include "../../config/db.php";
include "../includes/session_check.php";
$success = "";
$error = "";

if (isset($_POST['add'])) {
    if (
        empty($_POST['name']) || empty($_POST['category']) || empty($_POST['description']) ||
        empty($_POST['price']) || empty($_POST['discount']) || empty($_FILES['images']['name'][0])
    ) {
        $error = "Please fill in all fields and upload at least one image.";
    } else {
        $name     = mysqli_real_escape_string($conn, $_POST['name']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $desc     = mysqli_real_escape_string($conn, $_POST['description']);
        $price    = floatval($_POST['price']);
        $discount = floatval($_POST['discount']);
        $type     = isset($_POST['type']) && $_POST['type'] == "exclusive" ? "exclusive" : "normal";

        // Insert into products
        $sql = "INSERT INTO products (name, category, description, price, discount, type)
                VALUES ('$name', '$category', '$desc', '$price', '$discount', '$type')";

        if (mysqli_query($conn, $sql)) {
            $product_id = mysqli_insert_id($conn);

            // Exclusive fields only if type is exclusive
            if ($type == "exclusive") {
                if (!empty($_POST['stock_status'])) {
                    $stockStatus = $_POST['stock_status'];
                    mysqli_query($conn, "UPDATE products SET stock_status='$stockStatus' WHERE id='$product_id'");
                }
                if (!empty($_POST['colors'])) {
                    foreach ($_POST['colors'] as $color_id) {
                        $color_id = intval($color_id);
                        mysqli_query($conn, "INSERT INTO product_colors (product_id, color_id) VALUES ('$product_id', '$color_id')");
                    }
                }
                if (!empty($_POST['sizes'])) {
                    foreach ($_POST['sizes'] as $size_id) {
                        $size_id = intval($size_id);
                        mysqli_query($conn, "INSERT INTO product_sizes (product_id, size_id) VALUES ('$product_id', '$size_id')");
                    }
                }
            }

            // Image upload
            // $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/images/uploads/";
            $uploadDir = __DIR__ . "/../../images/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            foreach ($_FILES['images']['name'] as $key => $filename) {
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed)) {
                    $newName = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8) . "." . $ext;
                    $uploadPath = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['images']['tmp_name'][$key], $uploadPath)) {
                        mysqli_query($conn, "INSERT INTO product_images (product_id, image) VALUES ('$product_id', '$newName')");
                    }
                }
            }

            // ✅ Redirect according to type
            if ($type == "exclusive") {
                $success = "Product Added Successfully 🟢";
            } else {
                $success = "Product Added Successfully 🟢";
            }
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
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

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            height: auto;
            background-color: white;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
        }

        .form-control:focus,
        .form-select:focus {
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
            border: 2px dashed rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: white;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
            min-height: 120px;
            flex-direction: column;
        }

        .file-upload:hover .file-upload-label {
            border-color: var(--primary);
            background-color: rgba(94, 114, 228, 0.05);
        }

        .file-upload-label i {
            color: var(--primary);
            font-size: 2rem;
            margin-bottom: 10px;
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

        .badge-new {
            background: linear-gradient(135deg, var(--warning), var(--danger));
            color: white;
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
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

        .exclusive-section {
            background-color: rgba(94, 114, 228, 0.05);
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 1px solid rgba(94, 114, 228, 0.1);
        }

        .exclusive-section .section-title {
            border-bottom-color: rgba(94, 114, 228, 0.2);
        }

        .form-switch {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }

        /* Color Selection UI */
        .color-selection {
            margin-top: 1rem;
        }

        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
        }

        .color-option {
            position: relative;
            cursor: pointer;
            transition: var(--transition);
        }

        .color-swatch {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid transparent;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .color-swatch::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 50%);
            border-radius: 50%;
        }

        .color-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .color-option input[type="checkbox"]:checked+.color-swatch {
            border-color: var(--primary);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(94, 114, 228, 0.4);
        }

        .color-option input[type="checkbox"]:checked+.color-swatch::after {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: white;
            font-size: 18px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);
        }

        .color-name {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--dark);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            white-space: nowrap;
            opacity: 0;
            transition: var(--transition);
            pointer-events: none;
            z-index: 10;
        }

        .color-option:hover .color-name {
            opacity: 1;
            bottom: -30px;
        }

        /* Size Selection UI */
        .size-selection {
            margin-top: 1.5rem;
        }

        .size-options {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
        }

        .size-option {
            position: relative;
        }

        .size-option input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .size-button {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 60px;
            height: 50px;
            padding: 0 15px;
            border: 2px solid var(--gray);
            border-radius: 8px;
            background: white;
            color: var(--dark);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .size-option input[type="checkbox"]:checked+.size-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: var(--primary);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(94, 114, 228, 0.3);
        }

        .size-button:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        /* Selection Counter */
        .selection-counter {
            display: inline-block;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            text-align: center;
            line-height: 24px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
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

            .btn-primary,
            .btn-outline-secondary {
                width: 100%;
            }

            .form-col {
                min-width: 100%;
            }

            .color-swatch {
                width: 40px;
                height: 40px;
            }

            .size-button {
                min-width: 50px;
                height: 45px;
                padding: 0 12px;
                font-size: 14px;
            }
        }

        /* Animation for page load */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Custom checkbox styling */
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Image preview animation */
        .preview-box {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Loading spinner */
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</head>

<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-plus-circle me-2"></i> Add New Product</h1>
                    <p class="mb-0">Create a new product for your e-commerce store</p>
                </div>
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h4><i class="fas fa-box me-2"></i> Product Information</h4>
                <span class="badge-new">New</span>
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
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i> Basic Information
                    </div>

                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Product Name</label>
                                <div class="input-icon">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" name="name" class="form-control" placeholder="Enter product name" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Category</label>
                                <div class="input-icon">
                                    <i class="fas fa-folder"></i>
                                    <input type="text" name="category" class="form-control" placeholder="Enter category" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <div class="input-icon">
                            <i class="fas fa-align-left"></i>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter product description" required></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Type</label>
                        <div class="form-switch">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="typeToggle" name="type" value="exclusive">
                                <label class="form-check-label" for="typeToggle">
                                    <span id="typeLabel">Normal Product</span>
                                </label>
                            </div>
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
                                    <input type="number" step="0.01" name="price" class="form-control" placeholder="Enter price" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label class="form-label">Discount (%)</label>
                                <div class="input-icon">
                                    <i class="fas fa-percentage"></i>
                                    <input type="number" step="0.01" name="discount" class="form-control" placeholder="Enter discount" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4">
                        <i class="fas fa-images"></i> Product Images
                    </div>

                    <div class="form-group">
                        <label class="form-label">Product Images (Multiple Selection)</label>
                        <div class="file-upload">
                            <input type="file" name="images[]" id="images" multiple required>
                            <label for="images" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Choose images or drag them here</span>
                                <small class="text-muted d-block mt-1">jpg, jpeg, png, webp</small>
                            </label>
                        </div>
                        <div class="preview-container" id="preview-container"></div>
                    </div>

                    <!-- Exclusive Product Section -->
                    <div id="exclusiveSection" class="exclusive-section" style="display:none;">
                        <div class="section-title">
                            <i class="fas fa-star"></i> Exclusive Product Details
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stock Status</label>
                            <div class="input-icon">
                                <i class="fas fa-warehouse"></i>
                                <select name="stock_status" class="form-select" required>
                                    <option value="in">In Stock</option>
                                    <option value="out">Out of Stock</option>
                                </select>
                            </div>
                        </div>

                        <!-- Color Selection UI -->
                        <div class="color-selection">
                            <label class="form-label">
                                Available Colors
                                <span class="selection-counter" id="colorCounter">0</span>
                            </label>
                            <div class="color-options">
                                <?php
                                $res = mysqli_query($conn, "SELECT * FROM colors ORDER BY name ASC");
                                while ($row = mysqli_fetch_assoc($res)) {
                                    // Generate a random color if hex_code is not available
                                    $hex_code = isset($row['hex_code']) ? $row['hex_code'] : '#' . substr(md5(rand()), 0, 6);
                                    echo "
                                    <div class='color-option'>
                                        <input type='checkbox' name='colors[]' id='color_{$row['id']}' value='{$row['id']}' class='color-checkbox'>
                                        <label for='color_{$row['id']}' class='color-swatch' style='background-color: {$hex_code};'></label>
                                        <div class='color-name'>{$row['name']}</div>
                                    </div>
                                    ";
                                }
                                ?>
                            </div>
                        </div>

                        <!-- Size Selection UI -->
                        <div class="size-selection">
                            <label class="form-label">
                                Available Sizes
                                <span class="selection-counter" id="sizeCounter">0</span>
                            </label>
                            <div class="size-options">
                                <?php
                                $res = mysqli_query($conn, "SELECT * FROM sizes ORDER BY name ASC");
                                while ($row = mysqli_fetch_assoc($res)) {
                                    echo "
                                    <div class='size-option'>
                                        <input type='checkbox' name='sizes[]' id='size_{$row['id']}' value='{$row['id']}' class='size-checkbox'>
                                        <label for='size_{$row['id']}' class='size-button'>{$row['name']}</label>
                                    </div>
                                    ";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <!-- Exclusive Product Section -->

                    <div class="form-footer">
                        <button type="submit" name="add" class="btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Product
                        </button>
                        <a href="../index.php" class="btn-outline-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle product type
        document.getElementById("typeToggle").addEventListener("change", function() {
            const exclusiveSection = document.getElementById("exclusiveSection");
            const typeLabel = document.getElementById("typeLabel");

            if (this.checked) {
                exclusiveSection.style.display = "block";
                typeLabel.textContent = "Exclusive Product";
            } else {
                exclusiveSection.style.display = "none";
                typeLabel.textContent = "Normal Product";
            }
        });

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

            // Color selection counter
            const colorCheckboxes = document.querySelectorAll('.color-checkbox');
            const colorCounter = document.getElementById('colorCounter');

            function updateColorCounter() {
                const checked = document.querySelectorAll('.color-checkbox:checked').length;
                colorCounter.textContent = checked;
            }

            colorCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateColorCounter);
            });

            // Size selection counter
            const sizeCheckboxes = document.querySelectorAll('.size-checkbox');
            const sizeCounter = document.getElementById('sizeCounter');

            function updateSizeCounter() {
                const checked = document.querySelectorAll('.size-checkbox:checked').length;
                sizeCounter.textContent = checked;
            }

            sizeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSizeCounter);
            });
        });
    </script>
</body>
</html>