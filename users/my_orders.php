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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --amazon-blue: #131921;
            --amazon-orange: #FF9900;
            --amazon-light-orange: #FFD814;
            --amazon-dark-blue: #232F3E;
            --amazon-light-gray: #F7F7F7;
            --amazon-border: #D5D9D9;
            --amazon-text: #0F1111;
            --amazon-light-text: #565959;
            --amazon-star: #FFA41C;
            --border-radius: 4px;
            --transition: all 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #fff;
            color: var(--amazon-text);
            line-height: 1.5;
            font-size: 14px;
        }

        .orders-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .page-header {
            background: var(--amazon-blue);
            color: white;
            padding: 15px 20px;
            margin-bottom: 20px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            border-radius: var(--border-radius);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-title {
            font-size: 24px;
            font-weight: 400;
            margin: 0;
        }

        .back-btn {
            background: var(--amazon-orange);
            color: var(--amazon-blue);
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-weight: 400;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .back-btn:hover {
            background: var(--amazon-light-orange);
            color: var(--amazon-blue);
        }

        /* Orders Card */
        .orders-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: none;
            margin-bottom: 20px;
            border: 1px solid var(--amazon-border);
        }

        .orders-card-header {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
            padding: 14px 20px;
            font-weight: 500;
            font-size: 16px;
            border-bottom: 1px solid var(--amazon-border);
        }

        .table-container {
            overflow-x: auto;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead th {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
            font-weight: 500;
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
            border-bottom: 1px solid var(--amazon-border);
        }

        .orders-table tbody tr {
            border-bottom: 1px solid var(--amazon-border);
            transition: var(--transition);
        }

        .orders-table tbody tr:hover {
            background-color: var(--amazon-light-gray);
        }

        .orders-table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }

        /* Product Info */
        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-thumb {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border: 1px solid var(--amazon-border);
            border-radius: var(--border-radius);
            padding: 5px;
            background: white;
        }

        .product-name {
            font-weight: 400;
            color: var(--amazon-text);
            margin: 0;
            font-size: 14px;
            line-height: 1.4;
        }

        /* Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: var(--border-radius);
            font-size: 12px;
            font-weight: 400;
            text-align: center;
            display: inline-block;
            min-width: 80px;
        }

        .status-pending {
            background-color: #F0AD4E;
            color: white;
        }

        .status-success {
            background-color: #5CB85C;
            color: white;
        }

        .status-danger {
            background-color: #D9534F;
            color: white;
        }

        .status-info {
            background-color: #5BC0DE;
            color: white;
        }

        /* Feedback Button */
        .feedback-btn {
            background: var(--amazon-orange);
            color: var(--amazon-blue);
            border: none;
            padding: 6px 12px;
            border-radius: var(--border-radius);
            font-size: 12px;
            font-weight: 400;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
        }

        .feedback-btn:hover {
            background: var(--amazon-light-orange);
            color: var(--amazon-blue);
        }

        /* Empty Orders */
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: var(--amazon-light-text);
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--amazon-border);
        }

        .empty-orders i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--amazon-light-text);
        }

        .empty-orders h4 {
            font-weight: 400;
            margin-bottom: 15px;
            font-size: 20px;
        }

        .empty-orders p {
            max-width: 500px;
            margin: 0 auto;
        }

        .empty-orders .btn {
            margin-top: 20px;
            background: var(--amazon-orange);
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            color: var(--amazon-blue);
            font-weight: 400;
            text-decoration: none;
            display: inline-block;
        }

        .empty-orders .btn:hover {
            background: var(--amazon-light-orange);
            color: var(--amazon-blue);
        }

        /* Mobile Responsive Styles */
        @media (max-width: 991px) {
            .page-header {
                padding: 15px 20px;
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
                margin-bottom: 15px;
                border: 1px solid var(--amazon-border);
                border-radius: var(--border-radius);
                padding: 15px;
                background: white;
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
                font-weight: 500;
                color: var(--amazon-light-text);
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
            .orders-container {
                padding: 0 10px;
            }

            .page-header {
                padding: 12px 15px;
            }

            .page-title {
                font-size: 20px;
            }

            .product-thumb {
                width: 60px;
                height: 60px;
            }

            .status-badge {
                min-width: 70px;
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <h1 class="page-title">My Orders</h1>
        <a href="../index.php" class="back-btn">
            <i class="fas fa-arrow-left"></i> Back to Shopping
        </a>
    </div>

    <div class="orders-container">
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
                        <a href="../index.php" class="btn">
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