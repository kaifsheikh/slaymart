<?php
include "../../config/db.php";
include "../includes/session_check.php";
// DELETE logic
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $del = "DELETE FROM users WHERE id = $delete_id";
    if (mysqli_query($conn, $del)) {
        header("Location: manage_users.php?msg=deleted");
        exit;
    }
}
// Fetch approved users
$sql = "SELECT * FROM users WHERE status = 'approved'";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Approved Users - Admin Panel</title>
    
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
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-details h5 {
            margin-bottom: 0;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .user-details p {
            margin-bottom: 0;
            font-size: 0.8rem;
            color: var(--secondary);
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
            margin-right: 5px;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
        }
        
        .btn-edit {
            background-color: var(--info);
            border-color: var(--info);
        }
        
        .btn-edit:hover {
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
        
        .alert {
            border-radius: 0.35rem;
            border: none;
            padding: 1rem 1.25rem;
        }
        
        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid var(--info);
        }
        
        .alert-info {
            background-color: #e2e3e5;
            color: #41464b;
            border-left: 4px solid var(--secondary);
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
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.85rem;
            }
            
            .btn-action {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }
            
            .user-avatar {
                width: 35px;
                height: 35px;
                font-size: 0.9rem;
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
                    <h1><i class="fas fa-users-check me-2"></i> Manage Approved Users</h1>
                    <p>View and manage all approved users in the system</p>
                </div>
                <a href="../index.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Success Message -->
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> User deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- Users Table Card -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-table me-2"></i> Approved Users List</h4>
                    <div class="d-flex align-items-center">
                        <div class="input-group me-2" style="width: 250px;">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control form-control-sm" placeholder="Search users..." id="searchInput">
                        </div>
                        <button class="btn btn-sm btn-primary">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-container">
                        <table class="table table-hover" id="usersTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Registered At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?= $i++ ?></td>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                            </div>
                                            <div class="user-details">
                                                <h5><?= htmlspecialchars($row['name']) ?></h5>
                                                <p>ID: <?= $row['id'] ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'approved' ? 'success' : 'warning' ?>">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-action btn-edit text-white">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="?delete_id=<?= $row['id'] ?>" 
                                            class="btn btn-action btn-delete text-white"
                                            onclick="return confirmDelete();">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-slash"></i>
                        <h4>No Approved Users Found</h4>
                        <p>There are currently no approved users in the system.</p>
                        <a href="#" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i> Add New User
                        </a>
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
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Custom confirmation dialog for delete
        function confirmDelete() {
            return confirm('Are you sure you want to delete this user? This action cannot be undone.');
        }
        
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('usersTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const name = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                const email = rows[i].getElementsByTagName('td')[2].textContent.toLowerCase();
                
                if (name.includes(searchValue) || email.includes(searchValue)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>