<?php
include "../config/db.php";
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Enhanced email validation
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } elseif (strlen($email) > 100) {
        $errors[] = "Email address is too long (max 100 characters).";
    }
    
    // Enhanced password validation
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    } elseif (strlen($password) > 50) {
        $errors[] = "Password is too long (max 50 characters).";
    }
    
    if (empty($errors)) {
        // Using prepared statements to prevent SQL injection
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? AND role = 'user'");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: ../index.php");
                exit;
            } else {
                $errors[] = "Incorrect password. Please try again.";
            }
        } else {
            $errors[] = "No account found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaymart - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../images/logo/favicon.png" type="image/x-icon">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            min-height: 600px;
        }

        .login-image {
            flex: 1;
            background: url('https://picsum.photos/seed/slaymart/600/900') center/cover;
            position: relative;
            display: none;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
        }

        .image-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 1;
        }

        .image-content h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .image-content p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .login-form {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            color: #667eea;
            font-size: 2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .logo h1 i {
            font-size: 2.5rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            color: #2c3e50;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #7f8c8d;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 25px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .signup-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-top: 20px;
        }

        .signup-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: #27ae60;
            font-size: 0.85rem;
        }

        .security-badge i {
            font-size: 1rem;
        }

        /* Error Messages Styling */
        .error-container {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error-container ul {
            margin: 0;
            padding-left: 20px;
            color: #e53e3e;
        }

        .error-container li {
            margin-bottom: 5px;
        }

        /* Input Validation Styles */
        .form-control.error {
            border-color: #e53e3e;
            background: #fff5f5;
        }

        .form-control.success {
            border-color: #27ae60;
            background: #f0fff4;
        }

        /* Responsive Design */
        @media (min-width: 768px) {
            .login-image {
                display: block;
            }
            
            .login-form {
                padding: 60px;
            }
        }

        @media (max-width: 767px) {
            .login-container {
                max-width: 100%;
                min-height: auto;
            }
            
            .login-form {
                padding: 40px 30px;
            }
        }

        /* Loading Animation */
        .btn-login.loading {
            position: relative;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }

        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-image">
            <div class="image-content">
                <h1>Welcome Back!</h1>
                <p>Login to continue your shopping experience</p>
            </div>
        </div>
        
        <div class="login-form">
            <div class="logo">
                <h1><i class="fas fa-shopping-bag"></i> Slaymart</h1>
            </div>
            
            <div class="form-header">
                <h2>Login to Your Account</h2>
                <p>Enter your credentials to access your account</p>
            </div>
            
            <form method="POST" action="" id="loginForm">
                <!-- Error Messages -->
                <?php if (!empty($errors)) : ?>
                    <div class="error-container">
                        <ul>
                            <?php foreach ($errors as $error) : ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                    </div>
                    <div class="password-requirements">
                        <i class="fas fa-info-circle"></i>
                        <span>Must be at least 6 characters</span>
                    </div>
                </div>
                
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            
            <div class="signup-link">
                Don't have an account? <a href="register.php">Sign up here</a>
            </div>
            
            <div class="security-badge">
                <i class="fas fa-shield-alt"></i>
                <span>Your data is safe and secure with us</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handling
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-login');
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Client-side validation
            let isValid = true;
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email) {
                showError('email', 'Email is required');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showError('email', 'Please enter a valid email');
                isValid = false;
            } else if (email.length > 100) {
                showError('email', 'Email is too long');
                isValid = false;
            } else {
                showSuccess('email');
            }
            
            // Password validation
            if (!password) {
                showError('password', 'Password is required');
                isValid = false;
            } else if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters');
                isValid = false;
            } else if (password.length > 50) {
                showError('password', 'Password is too long');
                isValid = false;
            } else {
                showSuccess('password');
            }
            
            if (!isValid) {
                e.preventDefault();
                return;
            }
            
            // Show loading state
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });
        
        // Real-time validation
        document.getElementById('email').addEventListener('input', function() {
            const email = this.value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!email) {
                resetField('email');
            } else if (!emailRegex.test(email)) {
                showError('email', 'Please enter a valid email');
            } else if (email.length > 100) {
                showError('email', 'Email is too long');
            } else {
                showSuccess('email');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (!password) {
                resetField('password');
            } else if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters');
            } else if (password.length > 50) {
                showError('password', 'Password is too long');
            } else {
                showSuccess('password');
            }
        });
        
        // Validation helper functions
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('error');
            field.classList.remove('success');
            
            // Remove existing error message if any
            const existingError = field.parentElement.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
            
            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error';
            errorDiv.style.color = '#e53e3e';
            errorDiv.style.fontSize = '0.8rem';
            errorDiv.style.marginTop = '5px';
            errorDiv.textContent = message;
            field.parentElement.appendChild(errorDiv);
        }
        
        function showSuccess(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.add('success');
            field.classList.remove('error');
            
            // Remove error message if exists
            const existingError = field.parentElement.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }
        
        function resetField(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('error', 'success');
            
            // Remove error message if exists
            const existingError = field.parentElement.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }
        }
        
        // Input focus effects
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('i').style.color = '#667eea';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = '#7f8c8d';
            });
        });
    </script>
</body>
</html>