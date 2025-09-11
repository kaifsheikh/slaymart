<?php
include "../../config/db.php";
include "../includes/session_check.php";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $old_price = floatval($_POST['old_price']);
    $sold = (int)$_POST['sold'];
    $available = (int)$_POST['available'];
    $end_date = $_POST['end_date'];
    
    // Validate required fields
    if (empty($title) || empty($description) || $price <= 0 || empty($end_date)) {
        die("❌ Please fill all required fields correctly!");
    }
    
    // Image handling
    $imageDBPath = "";
    $originalName = "";
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../../images/deals/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $originalName = basename($_FILES["image"]["name"]);
        $fileType = pathinfo($originalName, PATHINFO_EXTENSION);
        $uniqueName = time() . "_" . uniqid() . "." . $fileType;
        $imagePath = $targetDir . $uniqueName;
        
        // Check file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            die("❌ Only JPG, JPEG, PNG & GIF files are allowed!");
        }
        
        // Check file size (5MB max)
        if ($_FILES["image"]["size"] > 5000000) {
            die("❌ File is too large. Max size is 5MB!");
        }
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $imageDBPath = $uniqueName;
        } else {
            die("❌ Image upload failed!");
        }
    } else {
        die("❌ Please upload an image!");
    }
    
    // Insert into database
    $query = "INSERT INTO deals (title, description, price, old_price, sold, available, image, original_name, end_date, status, created_at) 
              VALUES ('$title','$description','$price','$old_price','$sold','$available','$imageDBPath','$originalName','$end_date','active',NOW())";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('✅ Deal Added Successfully!'); window.location='view-deals.php';</script>";
    } else {
        die("❌ Database Error: " . mysqli_error($conn));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Deal - Admin Panel</title>
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
        
        /* Form Card */
        .form-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .form-card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 1.75rem;
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
        
        .card-header p {
            margin: 0.5rem 0 0 2.5rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        /* Form Elements */
        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .form-control, .form-select {
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
            outline: none;
        }
        
        .form-control::placeholder {
            color: #adb5bd;
        }
        
        /* Buttons */
        .btn {
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }
        
        .btn i {
            margin-right: 0.5rem;
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
        
        .btn-secondary {
            background-color: white;
            border: 1px solid var(--border-color);
            color: var(--dark-text);
        }
        
        .btn-secondary:hover {
            background-color: var(--light-bg);
            border-color: #adb5bd;
        }
        
        /* Image Upload Area */
        .image-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #fff;
        }
        
        .image-upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .image-upload-area i {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .image-upload-area p {
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .image-upload-area .text-muted {
            font-size: 0.9rem;
        }
        
        .image-preview {
            max-height: 200px;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        
        /* Price Input */
        .price-input-group {
            position: relative;
        }
        
        .price-input-group .input-group-text {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: transparent;
            border: none;
            color: #6c757d;
            font-weight: 500;
        }
        
        .price-input-group .form-control {
            padding-left: 2.5rem;
        }
        
        /* Form Sections */
        .form-section {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .form-card {
                margin-bottom: 1.5rem;
            }
        }
        
        /* Loading Spinner */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            visibility: hidden;
        }
        
        .spinner-overlay.active {
            visibility: visible;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3rem;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">

            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Main Form Card -->
                    <div class="card form-card">
                        <div class="card-header">
                            <h2><i class="bi bi-plus-circle"></i>Add New Deal</h2>
                            <p>Create a new promotional deal for your customers</p>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data" id="dealForm">
                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-info-circle"></i> Basic Information</h3>
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <label class="form-label"><i class="bi bi-tag"></i> Deal Title</label>
                                            <input type="text" name="title" class="form-control" placeholder="e.g. Summer Sale Special" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><i class="bi bi-calendar-event"></i> End Date</label>
                                            <input type="datetime-local" name="end_date" class="form-control" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label"><i class="bi bi-card-text"></i> Description</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Describe your deal in detail..." required></textarea>
                                    </div>
                                </div>
                                
                                <!-- Pricing Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-currency-dollar"></i> Pricing Information</h3>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-currency-dollar"></i> Current Price</label>
                                            <div class="price-input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-currency-exchange"></i> Original Price</label>
                                            <div class="price-input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="old_price" class="form-control" placeholder="0.00" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Inventory Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-box-seam"></i> Inventory Information</h3>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-check-circle"></i> Items Sold</label>
                                            <input type="number" name="sold" class="form-control" placeholder="Number of items sold" min="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-box-seam"></i> Available Stock</label>
                                            <input type="number" name="available" class="form-control" placeholder="Available quantity" min="0">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Image Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-image"></i> Deal Image</h3>
                                    <div class="mb-4">
                                        <label class="form-label"><i class="bi bi-image"></i> Upload Image</label>
                                        <div class="image-upload-area" onclick="document.getElementById('imageUpload').click()">
                                            <i class="bi bi-cloud-upload"></i>
                                            <p>Click to upload image or drag and drop</p>
                                            <p class="text-muted">PNG, JPG, GIF up to 5MB</p>
                                            <input type="file" name="image" id="imageUpload" class="d-none" accept="image/*" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                    <a href="view-deals.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Deals
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Add Deal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="spinner-overlay" id="spinnerOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image upload preview
        document.getElementById('imageUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const uploadArea = document.querySelector('.image-upload-area');
                    
                    // Hide the original content
                    uploadArea.querySelector('i').style.display = 'none';
                    uploadArea.querySelector('p').style.display = 'none';
                    uploadArea.querySelectorAll('p')[1].style.display = 'none';
                    
                    // Create and add the preview
                    const preview = document.createElement('div');
                    preview.innerHTML = `
                        <img src="${e.target.result}" class="img-fluid image-preview">
                        <p class="mb-0"><strong>${file.name}</strong></p>
                        <p class="text-muted small mb-0">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImage">
                            <i class="bi bi-trash"></i> Remove Image
                        </button>
                    `;
                    
                    // Prevent the preview from triggering the file input when clicked
                    preview.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                    
                    // Handle remove image button
                    preview.querySelector('#removeImage').addEventListener('click', function() {
                        document.getElementById('imageUpload').value = '';
                        uploadArea.innerHTML = `
                            <i class="bi bi-cloud-upload"></i>
                            <p>Click to upload image or drag and drop</p>
                            <p class="text-muted">PNG, JPG, GIF up to 5MB</p>
                            <input type="file" name="image" id="imageUpload" class="d-none" accept="image/*" required>
                        `;
                        
                        // Re-attach event listener
                        document.getElementById('imageUpload').addEventListener('change', arguments.callee);
                    });
                    
                    // Append the preview to the upload area
                    uploadArea.appendChild(preview);
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Form submission with loading state
        document.getElementById('dealForm').addEventListener('submit', function() {
            document.getElementById('spinnerOverlay').classList.add('active');
        });
    </script>
</body>
</html>