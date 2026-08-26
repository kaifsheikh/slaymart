<?php
include "../config/db.php";
$errors = [];
$success = "";
$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '../index.php';
if (!is_string($redirect) || !preg_match('#^(?:checkout\.php\?id=\d+|\.\./index\.php)$#', $redirect)) {
    $redirect = '../index.php';
}

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
                header("Location: " . $redirect);
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
    <title>Login - Slaymart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../images/logo/favicon.png" type="image/x-icon">
    <style>
        :root {
            --amazon-orange: #FF9900;
            --amazon-dark: #131921;
            --amazon-blue: #232F3E;
            --amazon-light: #F7F7F7;
            --amazon-gray: #DDDDDD;
            --text-dark: #0F1111;
            --text-light: #565959;
            --error-color: #CC0C39;
            --success-color: #007600;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--amazon-light);
            color: var(--text-dark);
            line-height: 1.5;
        }

        /* Header Section */
        .header {
            background-color: var(--amazon-dark);
            padding: 15px 0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            margin-right: 25px;
        }

        .logo-text {
            color: white;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .logo-text span {
            color: var(--amazon-orange);
        }

        .logo img {
            height: 35px;
            margin-right: 10px;
        }

        .header-divider {
            height: 30px;
            width: 1px;
            background-color: var(--amazon-gray);
            margin: 0 15px;
        }

        .header-text {
            color: #CCC;
            font-size: 0.9rem;
        }

        /* Main Content */
        .login-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            display: flex;
            flex-wrap: wrap;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .login-image {
            flex: 1;
            min-width: 300px;
            background: url('https://images.unsplash.com/photo-1579546929662-711aa81148cf?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80') center/cover;
            position: relative;
        }

        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, rgba(19, 25, 33, 0.8) 0%, rgba(35, 47, 62, 0.6) 100%);
        }

        .image-content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            color: white;
            z-index: 1;
        }

        .image-content h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .image-content p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 400px;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .benefit-item i {
            color: var(--amazon-orange);
            font-size: 1.2rem;
            margin-right: 15px;
            width: 20px;
        }

        .login-form {
            flex: 1;
            min-width: 300px;
            padding: 40px;
        }

        .form-header {
            margin-bottom: 25px;
        }

        .form-header h1 {
            font-size: 1.8rem;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .form-header p {
            color: var(--text-light);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--amazon-gray);
            border-radius: 4px;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--amazon-orange);
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
        }

        .form-control.error {
            border-color: var(--error-color);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: var(--text-light);
            margin-top: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background-color: var(--amazon-orange);
            color: var(--text-dark);
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background-color: #e88b00;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background-color: var(--amazon-gray);
        }

        .divider span {
            background-color: white;
            padding: 0 15px;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .new-account {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid var(--amazon-gray);
            margin-top: 20px;
        }

        .new-account p {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .btn-create {
            width: 100%;
            padding: 10px;
            background-color: white;
            color: var(--text-dark);
            border: 1px solid var(--amazon-gray);
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-create:hover {
            background-color: var(--amazon-light);
        }

        /* Error Messages Styling */
        .error-container {
            background-color: #fff5f5;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }

        .error-container ul {
            margin: 0;
            padding-left: 20px;
            color: var(--error-color);
        }

        .error-container li {
            margin-bottom: 5px;
        }

        .field-error {
            color: var(--error-color);
            font-size: 0.8rem;
            margin-top: 5px;
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
            border: 2px solid var(--text-dark);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }

        @keyframes spinner {
            to { transform: rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            
            .login-form {
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            .header-text {
                display: none;
            }
            
            .form-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="header-container">
            <a href="../index.php" class="logo">
                <div class="logo-text">Slay<span>mart</span></div>
            </a>
            <div class="header-divider"></div>
            <div class="header-text">Login to your account</div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="login-container">
        <div class="login-image">
            <div class="image-content">
                <h1>Welcome to Slaymart</h1>
                <p>Sign in to access your personalized shopping experience, track orders, and more.</p>
                
                <div class="benefit-item">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Fast and reliable delivery</span>
                </div>
                
                <div class="benefit-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Secure payment methods</span>
                </div>
                
                <div class="benefit-item">
                    <i class="fas fa-undo"></i>
                    <span>Easy returns and refunds</span>
                </div>
                
                <div class="benefit-item">
                    <i class="fas fa-tag"></i>
                    <span>Exclusive deals and offers</span>
                </div>
            </div>
        </div>
        
        <div class="login-form">
            <div class="form-header">
                <h1>Sign in</h1>
                <p>Enter your email and password to access your account</p>
            </div>
            
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8') ?>">
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
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    <div id="email-error" class="field-error"></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                    <div class="password-requirements">Minimum 6 characters</div>
                    <div id="password-error" class="field-error"></div>
                </div>
                
                <button type="submit" class="btn-login">Continue</button>
            </form>
            
            <div class="divider">
                <span>New to Slaymart?</span>
            </div>
            
            <div class="new-account">
                <p>Create an account to enjoy personalized shopping experience</p>
                <a href="register.php" class="btn-create">Create your Slaymart account</a>
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
                showFieldError('email', 'Email is required');
                isValid = false;
            } else if (!emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email');
                isValid = false;
            } else if (email.length > 100) {
                showFieldError('email', 'Email is too long');
                isValid = false;
            } else {
                clearFieldError('email');
            }
            
            // Password validation
            if (!password) {
                showFieldError('password', 'Password is required');
                isValid = false;
            } else if (password.length < 6) {
                showFieldError('password', 'Password must be at least 6 characters');
                isValid = false;
            } else if (password.length > 50) {
                showFieldError('password', 'Password is too long');
                isValid = false;
            } else {
                clearFieldError('password');
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
                clearFieldError('email');
            } else if (!emailRegex.test(email)) {
                showFieldError('email', 'Please enter a valid email');
            } else if (email.length > 100) {
                showFieldError('email', 'Email is too long');
            } else {
                clearFieldError('email');
            }
        });
        
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            
            if (!password) {
                clearFieldError('password');
            } else if (password.length < 6) {
                showFieldError('password', 'Password must be at least 6 characters');
            } else if (password.length > 50) {
                showFieldError('password', 'Password is too long');
            } else {
                clearFieldError('password');
            }
        });
        
        // Validation helper functions
        function showFieldError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId + '-error');
            
            field.classList.add('error');
            errorElement.textContent = message;
        }
        
        function clearFieldError(fieldId) {
            const field = document.getElementById(fieldId);
            const errorElement = document.getElementById(fieldId + '-error');
            
            field.classList.remove('error');
            errorElement.textContent = '';
        }
    </script>
</body>
</html>
