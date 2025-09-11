<?php
include "../../config/db.php";
include "../includes/session_check.php";
// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}
$result = mysqli_query($conn, "SELECT t.id, t.order_id, t.reference_no, t.amount, t.payment_method, t.status, o.fullname 
                               FROM transactions t
                               JOIN orders o ON t.order_id = o.id
                               ORDER BY t.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin Panel</title>
    
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
        
        .transaction-id {
            font-weight: 600;
            color: var(--dark);
        }
        
        .customer-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .reference-no {
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .amount {
            font-weight: 600;
            color: var(--success);
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }
        
        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 0.35rem;
            margin: 0.25rem;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-approve {
            background-color: var(--success);
            border-color: var(--success);
        }
        
        .btn-approve:hover {
            background-color: #17a673;
            border-color: #17a673;
        }
        
        .btn-reject {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-reject:hover {
            background-color: #d02a20;
            border-color: #d02a20;
        }
        
        .btn-update {
            background-color: var(--info);
            border-color: var(--info);
        }
        
        .btn-update:hover {
            background-color: #2c9faf;
            border-color: #2c9faf;
        }
        
        .btn-delete {
            background-color: var(--danger);
            border-color: var(--danger);
        }
        
        .btn-delete:hover {
            background-color: #d02a20;
            border-color: #d02a20;
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
                    <h1><i class="fas fa-credit-card me-2"></i> Transactions</h1>
                    <p>Manage and verify all payment transactions</p>
                </div>
                <a href="../index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Transactions Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="mb-0"><i class="fas fa-table me-2"></i> Transactions History</h4>
                    <div class="d-flex align-items-center mt-3 mt-md-0">
                        <div class="search-bar me-2">
                            <i class="fas fa-search"></i>
                            <input type="text" class="form-control" placeholder="Search transactions..." id="searchInput">
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
                        <table class="table table-hover" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Payment Method</th>
                                    <th>Transaction ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td data-label="ID">
                                            <span class="transaction-id">#<?= $row['id'] ?></span>
                                        </td>
                                        <td data-label="Order ID">
                                            <span class="badge bg-info"><?= $row['order_id'] ?></span>
                                        </td>
                                        <td data-label="Customer">
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-2">
                                                    <div class="avatar-circle">
                                                        <?= strtoupper(substr($row['fullname'], 0, 1)) ?>
                                                    </div>
                                                </div>
                                                <span class="customer-name"><?= htmlspecialchars($row['fullname']) ?></span>
                                            </div>
                                        </td>
                                        <td data-label="Payment Method">
                                            <span class="badge bg-secondary"><?= htmlspecialchars($row['payment_method']) ?></span>
                                        </td>
                                        <td data-label="Transaction ID">
                                            <span class="reference-no" title="<?= htmlspecialchars($row['reference_no']) ?>">
                                                <?= substr($row['reference_no'], 0, 4) . '...' . substr($row['reference_no'], -6) ?>
                                            </span>
                                        </td>
                                        <td data-label="Amount">
                                            <span class="amount">Rs. <?= number_format($row['amount']) ?></span>
                                        </td>
                                        <td data-label="Status">
                                            <?php
                                                if ($row['status'] == 'pending') {
                                                    echo '<span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Pending</span>';
                                                } elseif ($row['status'] == 'success') {
                                                    echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Approved</span>';
                                                } elseif ($row['status'] == 'failed') {
                                                    echo '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Rejected</span>';
                                                }
                                            ?>
                                        </td>
                                        <td data-label="Actions">
                                            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                                                <?php if($row['status'] == 'pending'): ?>
                                                    <a href="verify_transaction.php?id=<?= $row['id'] ?>&status=success" class="btn btn-action btn-approve text-white">
                                                        <i class="fas fa-check me-1"></i> Approve
                                                    </a>
                                                    <a href="verify_transaction.php?id=<?= $row['id'] ?>&status=failed" class="btn btn-action btn-reject text-white">
                                                        <i class="fas fa-times me-1"></i> Reject
                                                    </a>
                                                <?php else: ?>
                                                    <a href="update_transaction.php?id=<?= $row['id'] ?>" class="btn btn-action btn-update text-white">
                                                        <i class="fas fa-edit me-1"></i> Update
                                                    </a>
                                                    <a href="delete_transaction.php?id=<?= $row['id'] ?>" class="btn btn-action btn-delete text-white" onclick="return confirmDelete();">
                                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h4>No Transactions Found</h4>
                        <p>There are currently no transactions in the system.</p>
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
    
    <style>
        .user-avatar .avatar-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8rem;
        }
    </style>
    
    <script>
        // Custom confirmation dialog for delete
        function confirmDelete() {
            return confirm('Are you sure you want to delete this transaction? This action cannot be undone.');
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('transactionsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const customer = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                const reference = rows[i].getElementsByTagName('td')[4].textContent.toLowerCase();
                const orderId = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                
                if (customer.includes(searchValue) || reference.includes(searchValue) || orderId.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>