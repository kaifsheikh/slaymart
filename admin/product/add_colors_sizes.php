<?php
include "../../config/db.php"; // Database connection
$success = "";
$error = "";

// Delete Color
if (isset($_GET['delete_color'])) {
    $id = intval($_GET['delete_color']);
    mysqli_query($conn, "DELETE FROM colors WHERE id = $id");
    $success = "Color deleted successfully!";
}

// Delete Size
if (isset($_GET['delete_size'])) {
    $id = intval($_GET['delete_size']);
    mysqli_query($conn, "DELETE FROM sizes WHERE id = $id");
    $success = "Size deleted successfully!";
}

// Add Color
if (isset($_POST['add_color'])) {
    $color = trim($_POST['color']);
    if ($color == "") {
        $error = "Please enter a color name.";
    } else {
        $color = mysqli_real_escape_string($conn, $color);
        mysqli_query($conn, "INSERT INTO colors (name) VALUES ('$color')");
        $success = "Color '$color' added successfully!";
    }
}

// Add Size
if (isset($_POST['add_size'])) {
    $size = trim($_POST['size']);
    if ($size == "") {
        $error = "Please enter a size name.";
    } else {
        $size = mysqli_real_escape_string($conn, $size);
        mysqli_query($conn, "INSERT INTO sizes (name) VALUES ('$size')");
        $success = "Size '$size' added successfully!";
    }
}

// Fetch existing colors and sizes
$colors = mysqli_query($conn, "SELECT * FROM colors ORDER BY name ASC");
$sizes = mysqli_query($conn, "SELECT * FROM sizes ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colors & Sizes Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #5e72e4;
            --secondary: #825ee4;
            --info: #11cdef;
            --success: #2dce89;
            --warning: #fb6340;
            --danger: #f5365c;
            --light: #f7fafc;
            --dark: #32325d;
            --gray: #8898aa;
            --light-gray: #f4f5f7;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 15px 35px rgba(50, 50, 93, 0.1), 0 5px 15px rgba(0, 0, 0, 0.07);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fe;
            color: var(--dark);
            min-height: 100vh;
            padding-bottom: 30px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2rem;
        }
        
        .page-header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            border: none;
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 40px rgba(50, 50, 93, 0.12), 0 8px 20px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header h4 {
            font-weight: 600;
            color: var(--dark);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .form-control {
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: var(--transition);
            height: auto;
            background-color: white;
            box-shadow: 0 1px 3px rgba(50, 50, 93, 0.05);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(94, 114, 228, 0.1);
            background-color: white;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(94, 114, 228, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(94, 114, 228, 0.4);
            background: linear-gradient(135deg, #4c63d2, #7549d9);
        }
        
        .btn-danger {
            background-color: var(--danger);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(245, 54, 92, 0.3);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(245, 54, 92, 0.4);
            background-color: #ec0c38;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(45, 206, 137, 0.1);
            color: #0a5c3c;
            border-left: 4px solid var(--success);
        }
        
        .alert-danger {
            background-color: rgba(245, 54, 92, 0.1);
            color: #a02639;
            border-left: 4px solid var(--danger);
        }
        
        .list-group {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .list-group-item {
            border: none;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: var(--transition);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .list-group-item:last-child {
            border-bottom: none;
        }
        
        .list-group-item:hover {
            background-color: rgba(94, 114, 228, 0.05);
        }
        
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--light-gray);
        }
        
        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .btn-back:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-2px);
        }
        
        .management-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .management-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .management-card .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .list-container {
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
            margin-top: 1rem;
        }
        
        /* Mobile Responsive Styles */
        @media (max-width: 768px) {
            .page-header {
                padding: 30px 0;
                margin-bottom: 20px;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .management-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            .list-container {
                max-height: 300px;
            }
        }
        
        /* Animation for page load */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .card:nth-child(2) {
            animation-delay: 0.1s;
        }
    </style>
</head>
<body>
    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-palette me-2"></i> Colors & Sizes Management</h1>
                    <p class="mb-0">Manage product colors and sizes for your store</p>
                </div>
                <a href="../index.php" class="btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- Success Message -->
        <?php if($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i>
                <div><?= $success ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Error Message -->
        <?php if($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <div><?= $error ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Management Grid -->
        <div class="management-grid">
            <!-- Colors Management -->
            <div class="card management-card">
                <div class="card-header">
                    <h4><i class="fas fa-palette me-2"></i> Colors Management</h4>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-paint-brush"></i></span>
                                <input type="text" name="color" class="form-control" placeholder="Enter color name" required>
                            </div>
                        </div>
                        <button type="submit" name="add_color" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle"></i> Add Color
                        </button>
                    </form>
                    
                    <div class="section-title mt-4">
                        <i class="fas fa-list"></i> Existing Colors
                    </div>
                    
                    <div class="list-container">
                        <?php if(mysqli_num_rows($colors) > 0): ?>
                            <ul class="list-group">
                                <?php while($row = mysqli_fetch_assoc($colors)): ?>
                                    <li class="list-group-item">
                                        <span><i class="fas fa-circle me-2" style="color: #<?= substr(md5($row['name']), 0, 6) ?>"></i><?= $row['name'] ?></span>
                                        <a href="?delete_color=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this color?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-palette"></i>
                                <p>No colors added yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sizes Management -->
            <div class="card management-card">
                <div class="card-header">
                    <h4><i class="fas fa-ruler-combined me-2"></i> Sizes Management</h4>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" name="size" class="form-control" placeholder="Enter size name" required>
                            </div>
                        </div>
                        <button type="submit" name="add_size" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle"></i> Add Size
                        </button>
                    </form>
                    
                    <div class="section-title mt-4">
                        <i class="fas fa-list"></i> Existing Sizes
                    </div>
                    
                    <div class="list-container">
                        <?php if(mysqli_num_rows($sizes) > 0): ?>
                            <ul class="list-group">
                                <?php while($row = mysqli_fetch_assoc($sizes)): ?>
                                    <li class="list-group-item">
                                        <span><i class="fas fa-tag me-2"></i><?= $row['name'] ?></span>
                                        <a href="?delete_size=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this size?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-ruler-combined"></i>
                                <p>No sizes added yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>