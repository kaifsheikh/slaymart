<?php
include '../../config/db.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    if (empty($email) || empty($password)) {
        $errors[] = "Please fill in all fields.";
    } else {
        $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin' LIMIT 1";
        $result = mysqli_query($conn, $query);
        if ($result && mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);
            if ($admin['status'] !== "approved") {
                $errors[] = "Your account is not approved.";
            } elseif (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['role'] = $admin['role'];
                header("Location: ../index.php");
                exit;
            } else {
                $errors[] = "Invalid credentials.";
            }
        } else {
            $errors[] = "Admin account not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - E-Commerce Panel</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .login-header .logo {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
        }
        
        .login-header h3 {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid #e3e6f0;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
            height: auto;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .input-group-text {
            background-color: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-right: none;
            color: var(--secondary);
            border-radius: 8px 0 0 8px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 8px 8px 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary) 0%, var(--info) 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .form-check-label {
            font-size: 14px;
            color: var(--secondary);
        }
        
        .forgot-password {
            font-size: 14px;
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: var(--info);
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .footer a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .footer a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 576px) {
            .login-body {
                padding: 30px 20px;
            }
            
            .login-header {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h3>Admin Panel</h3>
                <p>E-Commerce Management System</p>
            </div>
            <div class="login-body">
                <!-- Error Messages -->
                <?php if (!empty($errors)) : ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required placeholder="Enter your email">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="Enter your password">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                            </label>
                        </div>
                        <a href="#" class="forgot-password">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> E-Commerce Admin Panel. All rights reserved.</p>
            <p>Designed by <a href="#">Your Company</a></p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive enhancements
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
            
            // Add focus effects to inputs
            const inputs = document.querySelectorAll('.form-control');
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