<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Session Check

// ✅ Fetch products with their images
$query = "SELECT p.*, GROUP_CONCAT(pi.image) AS images 
          FROM products p 
          LEFT JOIN product_images pi ON p.id = pi.product_id 
          GROUP BY p.id 
          ORDER BY p.id DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Products - Admin Panel</title>

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

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
        }

        .card:hover {
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
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h4 {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table-container {
            overflow-x: auto;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            white-space: nowrap;
            background-color: var(--light-gray);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid rgba(0, 0, 0, 0.02);
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background-color: rgba(94, 114, 228, 0.05);
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
        }

        .product-image:hover {
            transform: scale(1.1);
        }

        .product-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }

        .product-id {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .product-category {
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 20px;
            background: rgba(17, 205, 239, 0.1);
            color: var(--info);
            font-weight: 500;
            display: inline-block;
        }

        .product-description {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.9rem;
            color: var(--gray);
        }

        .price-container {
            display: flex;
            flex-direction: column;
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

        .discount {
            font-weight: 600;
            color: var(--warning);
            font-size: 1rem;
        }

        .btn-action {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 8px;
            margin-right: 5px;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-edit {
            background-color: var(--info);
            border-color: var(--info);
            color: white;
            box-shadow: 0 4px 6px rgba(17, 205, 239, 0.2);
        }

        .btn-edit:hover {
            background-color: #0da5c0;
            border-color: #0da5c0;
            box-shadow: 0 7px 14px rgba(17, 205, 239, 0.3);
        }

        .btn-delete {
            background-color: var(--danger);
            border-color: var(--danger);
            color: white;
            box-shadow: 0 4px 6px rgba(245, 54, 92, 0.2);
        }

        .btn-delete:hover {
            background-color: #ec0c38;
            border-color: #ec0c38;
            box-shadow: 0 7px 14px rgba(245, 54, 92, 0.3);
        }

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 300px;
        }

        .search-bar input {
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border-radius: 50px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            font-size: 0.9rem;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        .btn-add {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            padding: 0.75rem 1.25rem;
            border-radius: 50px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(94, 114, 228, 0.2);
            transition: var(--transition);
        }

        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(94, 114, 228, 0.3);
            background: linear-gradient(135deg, #4c63d2, #7549d9);
        }

        .empty-state {
            padding: 4rem 1rem;
            text-align: center;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .empty-state h4 {
            color: var(--dark);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .empty-state p {
            color: var(--gray);
            max-width: 400px;
            margin: 0 auto 1.5rem;
        }

        .product-images-container {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .product-images-container img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
        }

        .product-images-container img:hover {
            transform: scale(1.1);
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

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stats-card h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stats-card p {
            color: var(--gray);
            margin: 0;
        }

        .stats-card i {
            font-size: 2.5rem;
            color: rgba(94, 114, 228, 0.2);
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

            .card-header {
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
            }

            .search-bar {
                max-width: 100%;
                width: 100%;
                margin-bottom: 10px;
            }

            .table thead {
                display: none;
            }

            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid rgba(0, 0, 0, 0.05);
                border-radius: var(--border-radius);
                box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
                padding: 15px;
                background-color: white;
            }

            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 0;
                border: none !important;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
            }

            .table tbody td:last-child {
                border-bottom: none !important;
            }

            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-right: 10px;
                font-size: 0.9rem;
            }

            .product-image {
                width: 80px;
                height: 80px;
            }

            .btn-action {
                padding: 0.4rem 0.6rem;
                font-size: 0.8rem;
            }

            .product-description {
                max-width: 100%;
                white-space: normal;
                overflow: visible;
                text-overflow: unset;
            }

            .stats-card {
                flex-direction: column;
                text-align: center;
                gap: 15px;
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

        .stats-card,
        .card {
            animation: fadeIn 0.5s ease forwards;
        }

        .card {
            animation-delay: 0.1s;
        }
    </style>
</head>

<body>
    <div class="container my-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1><i class="fas fa-boxes me-2"></i> All Products</h1>
            <a href="../index.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i> Back</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between">
                <h4><i class="fas fa-table me-2"></i> Products Inventory</h4>
                <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
            </div>
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="productsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Images</th>
                                    <th>Exclusive Options</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)) {
                                    $images = !empty($row['images']) ? explode(",", $row['images']) : [];
                                    $originalPrice = floatval($row['price']);
                                    $discount = floatval($row['discount']);
                                    $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);
                                ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if (!empty($images)) { ?>
                                                    <img src="../../images/uploads/<?= $images[0] ?>" class="rounded" width="50" height="50">
                                                <?php } ?>
                                                <div class="ms-2">
                                                    <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                                                    <small>ID: <?= $row['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($row['category']) ?></td>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td>
                                            <?php if ($discount > 0): ?>
                                                <span class="text-muted text-decoration-line-through">Rs. <?= number_format($originalPrice, 2) ?></span><br>
                                                <span class="fw-bold text-success">Rs. <?= number_format($discountedPrice, 2) ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold">Rs. <?= number_format($originalPrice, 2) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-info"><?= $discount ?>%</span></td>
                                        <td>
                                            <?php foreach ($images as $img) { ?>
                                                <img src="../../images/uploads/<?= $img ?>" class="rounded" width="40" height="40">
                                            <?php } ?>
                                        </td>
                                        
                                        <td>
                                            <?php if ($row['type'] == 'exclusive'): ?>
                                                <a href="colors_sizes.php?product_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1">
                                                    <i class="fas fa-palette"></i> Colors
                                                </a><br>
                                                <a href="colors_sizes.php?product_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning mb-1">
                                                    <i class="fas fa-ruler-combined"></i> Sizes
                                                </a><br>
                                                <a href="colors_sizes.php?product_id=<?= $row['id'] ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-warehouse"></i> Stock
                                                </a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Normal Product</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <a href="product_update.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                            <a href="product_delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmDelete();"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center p-5 text-muted">
                        <i class="fas fa-box-open fa-2x"></i>
                        <h4>No Products Found</h4>
                        <p>Add your first product to get started.</p>
                        <a href="add_product.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete() {
            return confirm('Are you sure you want to delete this product? This action cannot be undone.');
        }
    </script>
</body>

</html>