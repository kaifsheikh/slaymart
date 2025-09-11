<?php
include "../../config/db.php";
include "../includes/session_check.php";

// Fetch deals from database
$result = mysqli_query($conn, "SELECT * FROM deals ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Deals - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --warning-color: #ffbe0b;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --border-color: #e9ecef;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Poppins', sans-serif;
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navigation */
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .navbar-brand i {
            margin-right: 0.5rem;
            font-size: 1.8rem;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            color: var(--dark-text) !important;
            margin: 0 0.5rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .navbar-nav .nav-link i {
            margin-right: 0.4rem;
        }
        
        .navbar-nav .nav-link:hover {
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color) !important;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 2rem 0;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-weight: 700;
            font-size: 2rem;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .breadcrumb-item a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb-item.active {
            color: #6b7280;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.5rem;
            border: none;
        }
        
        .card-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
        }
        
        .card-header h2 i {
            margin-right: 0.75rem;
        }
        
        /* Table */
        .table-container {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            white-space: nowrap;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-color);
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* Deal Image */
        .deal-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* Status Badge */
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
        }
        
        .status-active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .status-expired {
            background-color: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            font-size: 0.875rem;
        }
        
        .btn i {
            margin-right: 0.4rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border: none;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e6a700;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 190, 11, 0.4);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border: none;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #e61254;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(247, 37, 133, 0.4);
        }
        
        /* Price Display */
        .current-price {
            font-weight: 600;
            color: var(--success-color);
            font-size: 1.1rem;
        }
        
        .old-price {
            text-decoration: line-through;
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        /* Stock Display */
        .stock-info {
            display: flex;
            flex-direction: column;
        }
        
        .stock-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }
        
        .stock-value {
            font-weight: 600;
        }
        
        .stock-low {
            color: var(--danger-color);
        }
        
        .stock-medium {
            color: var(--warning-color);
        }
        
        .stock-high {
            color: var(--success-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }
            
            .deal-image {
                width: 60px;
                height: 60px;
            }
            
            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
            
            .btn i {
                margin-right: 0.3rem;
            }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        /* Search Bar */
        .search-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
        
        .search-input {
            border-radius: 2rem;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem 0.75rem 3rem;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }
        
        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }
    </style>
</head>
<body>
    

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <!-- Content Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-bag-check"></i> Deals Management</h2>
                    <a href="add-deal.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Deal
                    </a>
                </div>
                <div class="card-body p-4">
                    <!-- Search Bar -->
                    <div class="search-container">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control search-input" id="searchInput" placeholder="Search deals by title...">
                    </div>
                    
                    <!-- Deals Table -->
                    <div class="table-container">
                        <?php if (mysqli_num_rows($result) > 0) { ?>
                        <table class="table table-hover" id="dealsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>End Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($deal = mysqli_fetch_assoc($result)) { 
                                    // Calculate deal status
                                    $end_date = new DateTime($deal['end_date']);
                                    $current_date = new DateTime();
                                    $status = $end_date > $current_date ? 'active' : 'expired';
                                    
                                    // Calculate stock status
                                    $stock_percentage = $deal['available'] > 0 ? ($deal['sold'] / $deal['available']) * 100 : 100;
                                    $stock_class = $stock_percentage >= 80 ? 'stock-low' : ($stock_percentage >= 50 ? 'stock-medium' : 'stock-high');
                                ?>
                                <tr>
                                    <td><?= $deal['id'] ?></td>
                                    <td>
                                        <img src="../../images/deals/<?= $deal['image'] ?>" alt="<?= htmlspecialchars($deal['title']) ?>" class="deal-image">
                                    </td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($deal['title']) ?></div>
                                        <div class="text-muted small"><?= substr(htmlspecialchars($deal['description']), 0, 50) ?>...</div>
                                    </td>
                                    <td>
                                        <div class="current-price">$<?= number_format($deal['price'], 2) ?></div>
                                        <div class="old-price">$<?= number_format($deal['old_price'], 2) ?></div>
                                    </td>
                                    <td>
                                        <div class="stock-info">
                                            <div class="stock-label">Available</div>
                                            <div class="stock-value <?= $stock_class ?>"><?= $deal['available'] ?></div>
                                        </div>
                                        <div class="stock-info mt-2">
                                            <div class="stock-label">Sold</div>
                                            <div class="stock-value"><?= $deal['sold'] ?></div>
                                        </div>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($deal['end_date'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $status ?>">
                                            <i class="bi bi-<?= $status == 'active' ? 'check-circle' : 'x-circle' ?>"></i>
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="edit-deal.php?id=<?= $deal['id'] ?>" class="btn btn-warning btn-sm">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <a href="delete-deal.php?id=<?= $deal['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this deal?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <?php } else { ?>
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <h3>No deals found</h3>
                            <p class="text-muted">Start by adding a new deal to your inventory.</p>
                            <a href="add-deal.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add New Deal
                            </a>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#dealsTable tbody tr');
            
            tableRows.forEach(row => {
                const title = row.querySelector('td:nth-child(3) div:first-child').textContent.toLowerCase();
                if (title.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>