<?php
include "../../config/db.php";
include "../includes/session_check.php";
// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}
// Error show karne ke liye
error_reporting(E_ALL);
ini_set('display_errors', 1);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $subtitle = mysqli_real_escape_string($conn, $_POST['subtitle']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $imageDBPath = "";
    $originalName = "";
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../images/banners/";
        $originalName = basename($_FILES["image"]["name"]); // Original name
        $uniqueName = time() . "_" . $originalName; // Unique save
        $imagePath = $targetDir . $uniqueName;
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $imageDBPath = $uniqueName;
        } else {
            die("Image upload failed. Please try again.");
        }
    } else {
        die("Please upload an image for the banner.");
    }
    $query = "INSERT INTO banners (title, subtitle, price, image, original_name, status) 
              VALUES ('$title', '$subtitle', '$price', '$imageDBPath', '$originalName', 'active')";
    mysqli_query($conn, $query) or die("DB Error: " . mysqli_error($conn));
    echo "<script>alert('✅ Banner added successfully!'); window.location='view-banners.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add Banner</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-success: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      --hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
      min-height: 100vh;
      padding-top: 20px;
    }
    
    .main-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 0 15px;
    }
    
    .page-header {
      background: white;
      border-radius: 12px;
      padding: 20px 30px;
      margin-bottom: 30px;
      box-shadow: var(--card-shadow);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .page-title {
      font-size: 24px;
      font-weight: 600;
      color: var(--dark);
      margin: 0;
      display: flex;
      align-items: center;
    }
    
    .page-title i {
      margin-right: 12px;
      color: var(--primary);
    }
    
    .back-btn {
      background-color: var(--light);
      color: var(--primary);
      border: 1px solid #e3e6f0;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
      display: flex;
      align-items: center;
    }
    
    .back-btn:hover {
      background-color: var(--primary);
      color: white;
    }
    
    .back-btn i {
      margin-right: 8px;
    }
    
    .form-container {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: var(--card-shadow);
    }
    
    .form-header {
      text-align: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e3e6f0;
    }
    
    .form-header h2 {
      font-size: 22px;
      font-weight: 600;
      color: var(--dark);
      margin: 0;
    }
    
    .form-header p {
      color: var(--secondary);
      margin-top: 8px;
      margin-bottom: 0;
    }
    
    .form-label {
      font-weight: 500;
      color: var(--dark);
      margin-bottom: 8px;
    }
    
    .form-control, .form-select {
      border-radius: 8px;
      border: 1px solid #e3e6f0;
      padding: 12px 15px;
      font-size: 15px;
      transition: all 0.3s;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
    }
    
    .image-upload-container {
      position: relative;
      border: 2px dashed #e3e6f0;
      border-radius: 8px;
      padding: 30px;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
    }
    
    .image-upload-container:hover {
      border-color: var(--primary);
      background-color: rgba(78, 115, 223, 0.05);
    }
    
    .image-upload-container i {
      font-size: 48px;
      color: var(--secondary);
      margin-bottom: 15px;
    }
    
    .image-upload-container h5 {
      font-weight: 500;
      color: var(--dark);
      margin-bottom: 5px;
    }
    
    .image-upload-container p {
      color: var(--secondary);
      font-size: 14px;
      margin: 0;
    }
    
    .image-preview {
      margin-top: 15px;
      max-width: 100%;
      max-height: 200px;
      border-radius: 8px;
      display: none;
      box-shadow: var(--card-shadow);
    }
    
    .submit-btn {
      background: var(--gradient-primary);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      font-weight: 500;
      font-size: 16px;
      transition: all 0.3s;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
    }
    
    .submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: var(--hover-shadow);
    }
    
    .required-mark {
      color: var(--danger);
      margin-left: 4px;
    }
    
    .input-group-text {
      background-color: var(--light);
      border: 1px solid #e3e6f0;
      border-right: none;
    }
    
    .input-group .form-control {
      border-left: none;
    }
    
    @media (max-width: 768px) {
      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .form-container {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="main-container">
    <!-- Page Header -->
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-image"></i> Banner Management</h1>
      <a href="view-banners.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Banners</a>
    </div>
    
    <!-- Form Container -->
    <div class="form-container">
      <div class="form-header">
        <h2>Add New Banner</h2>
        <p>Fill in the details below to add a new banner to your website</p>
      </div>
      
      <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
        <div class="row mb-3">
          <div class="col-md-6">
            <label for="title" class="form-label">Banner Title <span class="required-mark">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required>
            <div class="invalid-feedback">Please enter banner title.</div>
          </div>
          <div class="col-md-6">
            <label for="subtitle" class="form-label">Subtitle <span class="required-mark">*</span></label>
            <input type="text" class="form-control" id="subtitle" name="subtitle" required>
            <div class="invalid-feedback">Please enter subtitle.</div>
          </div>
        </div>
        
        <div class="mb-4">
          <label for="price" class="form-label">Price <span class="required-mark">*</span></label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" class="form-control" id="price" name="price" required>
            <div class="invalid-feedback">Please enter price.</div>
          </div>
        </div>
        
        <div class="mb-4">
          <label for="image" class="form-label">Banner Image <span class="required-mark">*</span></label>
          <div class="image-upload-container" onclick="document.getElementById('image').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <h5>Click to upload image</h5>
            <p>JPG, PNG or GIF. Max size: 5MB</p>
            <input type="file" id="image" name="image" accept="image/*" required style="display: none;">
            <img id="image-preview" class="image-preview" alt="Image Preview">
          </div>
          <div class="invalid-feedback">Please upload an image.</div>
        </div>
        
        <div class="d-grid">
          <button type="submit" class="submit-btn">
            <i class="fas fa-plus-circle me-2"></i> Add Banner
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS for validation -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Bootstrap validation
    (() => {
      'use strict'
      const forms = document.querySelectorAll('.needs-validation')
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
    })()
    
    // Image preview functionality
    document.getElementById('image').addEventListener('change', function(e) {
      const file = e.target.files[0];
      const reader = new FileReader();
      
      reader.onload = function(event) {
        const preview = document.getElementById('image-preview');
        preview.src = event.target.result;
        preview.style.display = 'block';
      }
      
      if (file) {
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>