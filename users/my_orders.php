<?php
include('../config/db.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT o.*, 
                 p.name AS product_name, 
                 t.status AS transaction_status,
                 r.id AS review_id
          FROM orders o
          JOIN products p ON o.product_id = p.id
          LEFT JOIN transactions t ON o.id = t.order_id
          LEFT JOIN reviews r ON o.id = r.order_id
          WHERE o.user_id = '$user_id'
          ORDER BY o.created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="hi">

<head>
    <title>Slaymart - My Orders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .page-title i {
            color: #667eea;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .orders-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .orders-card-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .table-container {
            overflow-x: auto;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            padding: 15px;
            text-align: left;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e9ecef;
        }

        .orders-table tbody tr {
            border-bottom: 1px solid #f1f3f5;
            transition: all 0.2s ease;
        }

        .orders-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .orders-table tbody td {
            padding: 15px;
            vertical-align: middle;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #e9ecef;
        }

        .product-name {
            font-weight: 500;
            color: #2c3e50;
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 90px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .feedback-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .feedback-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-orders i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .empty-orders h4 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .empty-orders p {
            max-width: 500px;
            margin: 0 auto;
        }

        .empty-orders .btn {
            margin-top: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 10px 25px;
            border-radius: 8px;
        }

        .empty-orders .btn:hover {
            background: linear-gradient(135deg, #5a67d8, #6b5b95);
            color: white;
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .orders-table thead {
                display: none;
            }

            .orders-table,
            .orders-table tbody {
                display: block;
                width: 100%;
            }

            .orders-table tbody tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #e9ecef;
                border-radius: 10px;
                padding: 15px;
                background: white;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .orders-table tbody td {
                display: block;
                padding: 8px 0;
                text-align: left;
                border: none;
                position: relative;
                padding-left: 40%;
            }

            .orders-table tbody td:before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 35%;
                padding-right: 10px;
                font-weight: 600;
                color: #6c757d;
                text-align: left;
            }

            .product-info {
                padding-left: 0;
            }

            .product-info:before {
                display: none;
            }
        }

        @media (max-width: 576px) {
            body {
                padding: 10px;
            }

            .page-header {
                padding: 20px;
            }

            .page-title {
                font-size: 1.3rem;
            }

            .product-thumb {
                width: 50px;
                height: 50px;
            }

            .status-badge {
                min-width: 80px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <div class="orders-container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-shopping-bag"></i> My Orders</h1>
            <a href="../index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Shopping
            </a>
        </div>

        <div class="orders-card">
            <div class="orders-card-header">
                Order History
            </div>
            <div class="table-container">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Total Price</th>
                                <th>Payment Status</th>
                                <th>Delivery Status</th>
                                <th>Feedback</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <?php
                                // ✅ Use selected_image from orders table if available
                                if (!empty($row['selected_image'])) {
                                    $product_img = $row['selected_image'];
                                } else {
                                    // fallback → first product image
                                    $imgRes = mysqli_query($conn, "SELECT image FROM product_images WHERE product_id = {$row['product_id']} ORDER BY id ASC LIMIT 1");
                                    $imgRow = mysqli_fetch_assoc($imgRes);
                                    $product_img = $imgRow['image'] ?? "no-image.png";
                                }
                                ?>

                                <tr>
                                    <td data-label="Product">
                                        <div class="product-info">
                                            <img src="../images/uploads/<?= $product_img ?>" class="product-thumb" alt="<?= htmlspecialchars($row['product_name']) ?>">
                                            <p class="product-name"><?= htmlspecialchars($row['product_name']) ?></p>
                                        </div>
                                    </td>
                                    <td data-label="Quantity"><?= $row['quantity'] ?></td>
                                    <td data-label="Total Price"><strong>Rs. <?= number_format($row['price']) ?></strong></td>
                                    <td data-label="Payment Status">
                                        <?php
                                        if ($row['payment_method'] === 'COD') {
                                            echo '<span class="status-badge status-success">Cash on Delivery</span>';
                                        } else {
                                            $payment_status = $row['transaction_status'] ?? 'pending';
                                            switch ($payment_status) {
                                                case 'pending':
                                                    echo '<span class="status-badge status-pending">Pending</span>';
                                                    break;
                                                case 'success':
                                                    echo '<span class="status-badge status-success">Success</span>';
                                                    break;
                                                case 'failed':
                                                    echo '<span class="status-badge status-danger">Failed</span>';
                                                    break;
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td data-label="Delivery Status">
                                        <?php
                                        $delivery_status = $row['status'] ?? 'pending';
                                        switch ($delivery_status) {
                                            case 'pending':
                                                echo '<span class="status-badge status-pending">Pending</span>';
                                                break;
                                            case 'processing':
                                                echo '<span class="status-badge status-info">Processing</span>';
                                                break;
                                            case 'shipped':
                                                echo '<span class="status-badge status-info">Shipped</span>';
                                                break;
                                            case 'delivered':
                                                echo '<span class="status-badge status-success">Delivered</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="status-badge status-danger">Cancelled</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td data-label="Feedback">
                                        <?php if ($row['status'] == "delivered" && empty($row['review_id'])): ?>
                                            <a href="../feedback/give_feedback.php?order_id=<?= $row['id'] ?>" class="feedback-btn">Give Feedback</a>
                                        <?php elseif (!empty($row['review_id'])): ?>
                                            <span class="status-badge status-success">Given</span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Date"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-cart"></i>
                        <h4>No Orders Yet</h4>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here.</p>
                        <a href="../index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i> Start Shopping
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>