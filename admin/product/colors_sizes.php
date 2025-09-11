<?php
// Include database connection
require_once '../../config/db.php';
include "../includes/session_check.php";

// Get product ID from URL
$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    die("Product ID is required");
}

// Fetch product details
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

// Fetch all available colors
$colors_query = "SELECT * FROM colors";
$colors_result = $conn->query($colors_query);

// Fetch all available sizes
$sizes_query = "SELECT * FROM sizes";
$sizes_result = $conn->query($sizes_query);

// Fetch current product colors
$current_colors_query = "SELECT color_id FROM product_colors WHERE product_id = ?";
$stmt = $conn->prepare($current_colors_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$current_colors_result = $stmt->get_result();
$current_colors = [];
while ($row = $current_colors_result->fetch_assoc()) {
    $current_colors[] = $row['color_id'];
}

// Fetch current product sizes
$current_sizes_query = "SELECT size_id FROM product_sizes WHERE product_id = ?";
$stmt = $conn->prepare($current_sizes_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$current_sizes_result = $stmt->get_result();
$current_sizes = [];
while ($row = $current_sizes_result->fetch_assoc()) {
    $current_sizes[] = $row['size_id'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update stock status
    $stock_status = $_POST['stock_status'] ?? 'in';
    $update_stock = "UPDATE products SET stock_status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_stock);
    $stmt->bind_param("si", $stock_status, $product_id);
    $stmt->execute();
    
    // Update colors - delete existing and insert new
    $delete_colors = "DELETE FROM product_colors WHERE product_id = ?";
    $stmt = $conn->prepare($delete_colors);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    
    if (!empty($_POST['colors'])) {
        $insert_color = "INSERT INTO product_colors (product_id, color_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_color);
        foreach ($_POST['colors'] as $color_id) {
            $stmt->bind_param("ii", $product_id, $color_id);
            $stmt->execute();
        }
    }
    
    // Update sizes - delete existing and insert new
    $delete_sizes = "DELETE FROM product_sizes WHERE product_id = ?";
    $stmt = $conn->prepare($delete_sizes);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    
    if (!empty($_POST['sizes'])) {
        $insert_size = "INSERT INTO product_sizes (product_id, size_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_size);
        foreach ($_POST['sizes'] as $size_id) {
            $stmt->bind_param("ii", $product_id, $size_id);
            $stmt->execute();
        }
    }
    
    // Redirect back to product list or show success message
    header("Location: manage_product.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Product Options</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container py-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Manage Product: <?= htmlspecialchars($product['name']) ?>
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <!-- Stock Status Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-3"><i class="fas fa-warehouse me-2"></i>Stock Status</h5>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="stock_status" id="stock_in" 
                                    value="in" <?= $product['stock_status'] == 'in' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stock_in">
                                    <i class="fas fa-check-circle text-success me-1"></i> In Stock
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="stock_status" id="stock_out" 
                                    value="out" <?= $product['stock_status'] == 'out' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="stock_out">
                                    <i class="fas fa-times-circle text-danger me-1"></i> Out of Stock
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Colors Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-3"><i class="fas fa-palette me-2"></i>Available Colors</h5>
                            <div class="row">
                                <?php while ($color = $colors_result->fetch_assoc()): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="colors[]" value="<?= $color['id'] ?>" 
                                                        id="color_<?= $color['id'] ?>"
                                                        <?= in_array($color['id'], $current_colors) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="color_<?= $color['id'] ?>">
                                                        <?= htmlspecialchars($color['name']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sizes Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-3"><i class="fas fa-ruler-combined me-2"></i>Available Sizes</h5>
                            <div class="row">
                                <?php while ($size = $sizes_result->fetch_assoc()): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="sizes[]" value="<?= $size['id'] ?>" 
                                                        id="size_<?= $size['id'] ?>"
                                                        <?= in_array($size['id'], $current_sizes) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="size_<?= $size['id'] ?>">
                                                        <?= htmlspecialchars($size['name']) ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="d-flex justify-content-between">
                        <a href="products.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Products
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>