<?php
// session_start();
include "../config/db.php";
// Initialize error array
$errors = [];
$success = "";

// Form Submit Check
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Enhanced validation
    if (empty($name)) {
        $errors[] = "Full name is required.";
    } elseif (strlen($name) < 2) {
        $errors[] = "Name must be at least 2 characters.";
    } elseif (strlen($name) > 50) {
        $errors[] = "Name is too long (max 50 characters).";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name can only contain letters and spaces.";
    }
    
    if (empty($email)) {
        $errors[] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    } elseif (strlen($email) > 100) {
        $errors[] = "Email address is too long (max 100 characters).";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    } elseif (strlen($password) > 50) {
        $errors[] = "Password is too long (max 50 characters).";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/", $password)) {
        $errors[] = "Password must contain at least one uppercase letter, one lowercase letter, and one number.";
    }
    
    // Check if email already exists using prepared statement
    $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $email);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        $errors[] = "Email already registered.";
    }
    
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Use prepared statement for insertion
        $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, status, role) VALUES (?, ?, ?, 'approved', 'user')");
        mysqli_stmt_bind_param($insert_stmt, "sss", $name, $email, $hashedPassword);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $success = "Registration successful! You can now login.";
            // Clear form fields on success
            $_POST = [];
        } else {
            $errors[] = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaymart - Register</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../images/logo/favicon.png" type="image/x-icon">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 40%, #f093fb 100%);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden; /* Allow vertical scrolling */
        }

        /* Animated background shapes */
        .bg-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            top: 0;
            left: 0;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 20s infinite ease-in-out;
        }

        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 10%;
            left: 70%;
            animation-delay: 6s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            33% {
                transform: translateY(-30px) rotate(120deg);
            }
            66% {
                transform: translateY(30px) rotate(240deg);
            }
        }

        .register-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 40px); /* Account for body padding */
            padding: 20px 0;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            min-height: 650px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-image {
            flex: 1;
            background: url('https://picsum.photos/seed/slaymart-register/600/900') center/cover;
            position: relative;
            display: none;
        }

        .register-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(240, 147, 251, 0.9));
        }

        .image-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            color: white;
            z-index: 1;
            padding: 20px;
        }

        .image-content h1 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .image-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            line-height: 1.6;
        }

        .register-form {
            flex: 1;
            padding: 70px 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .logo {
            text-align: center;
            margin-bottom: 50px;
        }

        .logo h1 {
            background: linear-gradient(135deg, #667eea, #f093fb);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .logo h1 i {
            font-size: 3rem;
            -webkit-text-fill-color: #667eea;
        }

        .logo p {
            color: #7f8c8d;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            color: #2c3e50;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #7f8c8d;
            font-size: 1rem;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 30px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 12px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }

        .form-control {
            width: 100%;
            padding: 18px 18px 18px 50px;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #2c3e50;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .password-requirements {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-top: 12px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .requirement:last-child {
            margin-bottom: 0;
        }

        .requirement i {
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .requirement.valid {
            color: #27ae60;
            transform: translateX(5px);
        }

        .requirement.invalid {
            color: #e74c3c;
        }

        .btn-register {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea, #f093fb);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        .btn-register::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-register:hover::before {
            left: 100%;
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.95rem;
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .login-link:hover {
            background: #e9ecef;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #f093fb;
            text-decoration: underline;
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 25px;
            color: #27ae60;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .security-badge i {
            font-size: 1.1rem;
        }

        /* Error Messages Styling */
        .error-container {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            animation: shake 0.5s;
            box-shadow: 0 5px 15px rgba(229, 62, 62, 0.1);
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }

        .error-container ul {
            margin: 0;
            padding-left: 20px;
            color: #e53e3e;
        }

        .error-container li {
            margin-bottom: 8px;
            font-weight: 500;
        }

        /* Success Message Styling */
        .success-container {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            color: #27ae60;
            text-align: center;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(39, 174, 96, 0.1);
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            .register-image {
                display: block;
            }
            
            .register-form {
                padding: 70px 60px;
            }
        }

        @media (max-width: 767px) {
            .register-container {
                max-width: 100%;
                min-height: auto; /* Allow height to be auto on mobile */
                border-radius: 20px;
            }
            
            .register-form {
                padding: 50px 40px;
            }
            
            .logo h1 {
                font-size: 2rem;
            }
            
            .form-header h2 {
                font-size: 1.8rem;
            }
            
            .register-wrapper {
                min-height: auto;
                padding: 0;
            }
        }

        /* Loading Animation */
        .btn-register.loading {
            position: relative;
            color: transparent;
        }

        .btn-register.loading::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            top: 50%;
            left: 50%;
            margin-left: -12.5px;
            margin-top: -12.5px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.8s linear infinite;
        }

        @keyframes spinner {
            to { transform: rotate(360deg); }
        }

        /* Floating animation for form */
        .register-form {
            animation: floatForm 6s ease-in-out infinite;
        }

        @keyframes floatForm {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-image">
                <div class="image-content">
                    <h1>Join Slaymart</h1>
                    <p>Create your account and start shopping with exclusive deals and offers</p>
                </div>
            </div>
            
            <div class="register-form">
                <div class="logo">
                    <h1><i class="fas fa-shopping-bag"></i> Slaymart</h1>
                    <p>Your Ultimate Shopping Destination</p>
                </div>
                
                <div class="form-header">
                    <h2>Create Account</h2>
                    <p>Fill in the details to get started</p>
                </div>
                
                <form method="POST" action="" id="registerForm">
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
                    
                    <!-- Success Message -->
                    <?php if (!empty($success)) : ?>
                        <div class="success-container">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" class="form-control" id="name" placeholder="Enter your full name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                        </div>
                    </div>
                    
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
                            <div class="requirement" id="length-req">
                                <i class="fas fa-circle"></i>
                                <span>At least 6 characters</span>
                            </div>
                            <div class="requirement" id="uppercase-req">
                                <i class="fas fa-circle"></i>
                                <span>One uppercase letter</span>
                            </div>
                            <div class="requirement" id="lowercase-req">
                                <i class="fas fa-circle"></i>
                                <span>One lowercase letter</span>
                            </div>
                            <div class="requirement" id="number-req">
                                <i class="fas fa-circle"></i>
                                <span>One number</span>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register">Create Account</button>
                </form>
                
                <div class="login-link">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
                
                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Your data is safe and secure with us</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission handling
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-register');
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            // Client-side validation
            let isValid = true;
            
            // Name validation
            if (!name) {
                showError('name', 'Name is required');
                isValid = false;
            } else if (name.length < 2) {
                showError('name', 'Name must be at least 2 characters');
                isValid = false;
            } else if (name.length > 50) {
                showError('name', 'Name is too long');
                isValid = false;
            } else if (!/^[a-zA-Z\s]+$/.test(name)) {
                showError('name', 'Name can only contain letters and spaces');
                isValid = false;
            } else {
                showSuccess('name');
            }
            
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
            } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(password)) {
                showError('password', 'Password must meet all requirements');
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
        document.getElementById('name').addEventListener('input', function() {
            const name = this.value;
            
            if (!name) {
                resetField('name');
            } else if (name.length < 2) {
                showError('name', 'Name must be at least 2 characters');
            } else if (name.length > 50) {
                showError('name', 'Name is too long');
            } else if (!/^[a-zA-Z\s]+$/.test(name)) {
                showError('name', 'Name can only contain letters and spaces');
            } else {
                showSuccess('name');
            }
        });
        
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
            
            // Update password requirements
            updatePasswordRequirements(password);
            
            if (!password) {
                resetField('password');
            } else if (password.length < 6) {
                showError('password', 'Password must be at least 6 characters');
            } else if (password.length > 50) {
                showError('password', 'Password is too long');
            } else if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/.test(password)) {
                showError('password', 'Password must meet all requirements');
            } else {
                showSuccess('password');
            }
        });
        
        // Password requirements checker
        function updatePasswordRequirements(password) {
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const lowercaseReq = document.getElementById('lowercase-req');
            const numberReq = document.getElementById('number-req');
            
            // Length requirement
            if (password.length >= 6) {
                lengthReq.classList.add('valid');
                lengthReq.classList.remove('invalid');
                lengthReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lengthReq.classList.add('invalid');
                lengthReq.classList.remove('valid');
                lengthReq.querySelector('i').className = 'fas fa-times-circle';
            }
            
            // Uppercase requirement
            if (/[A-Z]/.test(password)) {
                uppercaseReq.classList.add('valid');
                uppercaseReq.classList.remove('invalid');
                uppercaseReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                uppercaseReq.classList.add('invalid');
                uppercaseReq.classList.remove('valid');
                uppercaseReq.querySelector('i').className = 'fas fa-times-circle';
            }
            
            // Lowercase requirement
            if (/[a-z]/.test(password)) {
                lowercaseReq.classList.add('valid');
                lowercaseReq.classList.remove('invalid');
                lowercaseReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                lowercaseReq.classList.add('invalid');
                lowercaseReq.classList.remove('valid');
                lowercaseReq.querySelector('i').className = 'fas fa-times-circle';
            }
            
            // Number requirement
            if (/\d/.test(password)) {
                numberReq.classList.add('valid');
                numberReq.classList.remove('invalid');
                numberReq.querySelector('i').className = 'fas fa-check-circle';
            } else {
                numberReq.classList.add('invalid');
                numberReq.classList.remove('valid');
                numberReq.querySelector('i').className = 'fas fa-times-circle';
            }
        }
        
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
            errorDiv.style.marginTop = '8px';
            errorDiv.style.fontWeight = '500';
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
                this.parentElement.querySelector('i').style.transform = 'translateY(-50%) scale(1.1)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('i').style.color = '#7f8c8d';
                this.parentElement.querySelector('i').style.transform = 'translateY(-50%) scale(1)';
            });
        });
    </script>
</body>
</html>