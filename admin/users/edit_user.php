<?php
include '../../config/db.php';
include '../includes/session_check.php';
// ✅ Agar form submit hua hai (Update Logic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = intval($_POST['id']);
    $name   = mysqli_real_escape_string($conn, $_POST['name']);
    $email  = mysqli_real_escape_string($conn, $_POST['email']);
    $role   = mysqli_real_escape_string($conn, $_POST['role']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    if (empty($name) || empty($email) || empty($role) || empty($status)) {
        die("❌ All fields are required.");
    }
    $query = "UPDATE users 
              SET name='$name', email='$email', role='$role', status='$status'
              WHERE id=$id";
    if (mysqli_query($conn, $query)) {
        header("Location: edit_user.php?id=$id&msg=updated");
        exit;
    } else {
        echo "❌ Error updating record: " . mysqli_error($conn);
    }
}
// ✅ Agar GET request hai to user ka data load karo
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    } else {
        die("❌ User not found!");
    }
} else {
    die("❌ No user ID provided!");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
    
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        .edit-card {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .edit-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1.5rem;
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
            border-radius: 0.35rem;
            border: 1px solid #e3e6f0;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s;
            height: auto;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            border: none;
            border-radius: 0.35rem;
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
        }
        
        .btn-secondary {
            background-color: var(--secondary);
            border: none;
            border-radius: 0.35rem;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #6b6d7d;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 0.35rem;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border-left: 4px solid var(--info);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 600;
            margin: 0 auto 1.5rem;
            box-shadow: 0 4px 10px rgba(78, 115, 223, 0.3);
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
            color: var(--secondary);
        }
        
        .input-icon .form-control,
        .input-icon .form-select {
            padding-left: 45px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 2rem;
        }
        
        .btn-group .btn {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .page-header {
                padding: 20px 0;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
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
                    <h1><i class="fas fa-user-edit me-2"></i> Edit User</h1>
                    <p>Update user information and account settings</p>
                </div>
                <a href="./users_manage.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i> Back to Users
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Edit User Card -->
        <div class="edit-card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user-cog me-2"></i> User Information</h4>
                    <span class="badge bg-<?= $user['status'] === 'approved' ? 'success' : 'warning' ?>">
                        <?= ucfirst($user['status']) ?>
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Success Message -->
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i> User updated successfully.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- User Avatar -->
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                
                <!-- Edit Form -->
                <form method="post">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <div class="input-icon">
                                    <i class="fas fa-user-tag"></i>
                                    <select name="role" class="form-select" required>
                                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <div class="input-icon">
                                    <i class="fas fa-toggle-on"></i>
                                    <select name="status" class="form-select" required>
                                        <option value="pending" <?= $user['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="approved" <?= $user['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update User
                        </button>
                        <a href="./users_manage.php" class="btn btn-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </form>
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
        
        // Add focus effects to inputs
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-control, .form-select');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });
    </script>
</body>
</html>