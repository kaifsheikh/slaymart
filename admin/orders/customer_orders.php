<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Session Check
$query = "SELECT o.id, o.fullname AS customer, o.quantity, o.price, o.status, o.created_at, 
                 p.name AS product_name
          FROM orders o 
          JOIN products p ON o.product_id = p.id 
          ORDER BY o.created_at DESC";

$result = mysqli_query($conn, $query);
// Allowed statuses with bootstrap colors and icons
$statusConfig = [
    'pending'    => ['color' => 'warning', 'icon' => 'clock'],
    'processing' => ['color' => 'info', 'icon' => 'cog'],
    'shipped'    => ['color' => 'secondary', 'icon' => 'truck'],
    'delivered'  => ['color' => 'success', 'icon' => 'check-circle']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Orders - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: var(--light);
            color: var(--dark);
            padding-bottom: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .page-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1.25rem;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid #e3e6f0;
            color: var(--dark);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table tbody tr {
            transition: all 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .order-id {
            font-weight: 600;
            color: var(--dark);
        }
        
        .product-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .customer-name {
            font-weight: 500;
        }
        
        .quantity {
            font-weight: 600;
            color: var(--info);
        }
        
        .price {
            font-weight: 600;
            color: var(--success);
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
        }
        
        .status-badge i {
            margin-right: 0.25rem;
        }
        
        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.35rem;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-view {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-view:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        
        .order-date {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .search-bar {
            position: relative;
        }
        
        .search-bar input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border-radius: 2rem;
            border: 1px solid #e3e6f0;
            transition: all 0.3s;
        }
        
        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .form-select-sm {
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }
        
        .form-select-sm:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .empty-state {
            padding: 3rem 1rem;
            text-align: center;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .empty-state h4 {
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: var(--secondary);
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .page-header {
                padding: 20px 0;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-header {
                padding: 1rem;
            }
            
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e3e6f0;
                border-radius: 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                padding: 15px;
                background-color: white;
            }
            
            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 0;
                border: none !important;
                border-bottom: 1px solid #f1f3f9 !important;
            }
            
            .table tbody td:last-child {
                border-bottom: none !important;
            }
            
            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--primary);
                margin-right: 10px;
            }
            
            .btn-action {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-shopping-cart me-2"></i> All Orders</h1>
                    <p>Manage and track all customer orders</p>
                </div>
                <a href="../index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Orders Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><i class="fas fa-table me-2"></i> Orders Management</h4>
                    <div class="d-flex align-items-center mt-3 mt-md-0">
                        <div class="search-bar me-2">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search orders..." id="searchInput">
                        </div>
                        <button class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-container">
                        <table class="table table-hover" id="ordersTable">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th>Product</th>
                                    <th>Customer</th>
                                    <th>Quantity</th>
                                    <th>Price (Total)</th>
                                    <th>Status</th>
                                    <th>View Order</th>
                                    <th>Placed On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td data-label="ID">
                                            <span class="order-id">#<?= $row['id'] ?></span>
                                        </td>
                                        <td data-label="Product">
                                            <span class="product-name"><?= htmlspecialchars($row['product_name']) ?></span>
                                        </td>
                                        <td data-label="Customer">
                                            <span class="customer-name"><?= htmlspecialchars($row['customer']) ?></span>
                                        </td>
                                        <td data-label="Quantity">
                                            <span class="quantity"><?= $row['quantity'] ?></span>
                                        </td>
                                        <td data-label="Price (Total)">
                                            <span class="price">PKR <?= number_format($row['price'], 2) ?></span>
                                        </td>
                                        <td data-label="Status">
                                            <form action="update_status.php" method="GET" class="status-form">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                    <?php foreach ($statusConfig as $status => $config): ?>
                                                        <option value="<?= $status ?>" <?= $row['status'] === $status ? 'selected' : '' ?>>
                                                            <?= ucfirst($status) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </form>
                                        </td>
                                        <td data-label="View Order">
                                            <a href="view_order_detail.php?id=<?= $row['id'] ?>" class="btn btn-action btn-view text-white">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </td>
                                        <td data-label="Placed On">
                                            <span class="order-date"><?= date('d-M-Y', strtotime($row['created_at'])) ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-basket"></i>
                        <h4>No Orders Found</h4>
                        <p>There are currently no orders in the system.</p>
                        <a href="../index.php" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('ordersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const orderId = rows[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                const product = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const customer = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                
                if (orderId.includes(searchValue) || product.includes(searchValue) || customer.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>