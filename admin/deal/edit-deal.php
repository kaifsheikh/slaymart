<?php
include "../../config/db.php";
include "../includes/session_check.php";

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Deal ID validate
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid Deal ID");
}
$id = intval($_GET['id']);

// Deal fetch
$result = mysqli_query($conn, "SELECT * FROM deals WHERE id=$id");
if (mysqli_num_rows($result) == 0) {
    die("❌ Deal not found!");
}
$deal = mysqli_fetch_assoc($result);

// Update logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $old_price = mysqli_real_escape_string($conn, $_POST['old_price']);
    $sold = (int) $_POST['sold'];
    $available = (int) $_POST['available'];
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $imageDBPath = $deal['image']; // Purani image default
    $originalName = $deal['original_name'];
    
    // Agar nayi image upload hui hai
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../../images/deals/";
        $originalName = basename($_FILES["image"]["name"]);
        $uniqueName = time() . "_" . $originalName;
        $imagePath = $targetDir . $uniqueName;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            // Purani image delete karo
            if (!empty($deal['image']) && file_exists($targetDir . $deal['image'])) {
                unlink($targetDir . $deal['image']);
            }
            $imageDBPath = $uniqueName;
        } else {
            die("❌ Image upload failed.");
        }
    }
    
    // Update query
    $query = "UPDATE deals 
              SET title='$title', description='$description', price='$price', old_price='$old_price',
                  sold='$sold', available='$available', end_date='$end_date',
                  image='$imageDBPath', original_name='$originalName', status='$status'
              WHERE id=$id";
    
    mysqli_query($conn, $query) or die("DB Error: " . mysqli_error($conn));
    echo "<script>alert('✅ Deal updated successfully!'); window.location='view-deals.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Deal - Admin Panel</title>
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
        
        .page-header {
            margin-bottom: 2rem;
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
        
        /* Image Preview */
        .image-preview-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .current-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 3px solid white;
        }
        
        .image-label {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: var(--primary-color);
            color: white;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        /* Image Upload Area */
        .image-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 0.75rem;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #fff;
            margin-top: 1rem;
        }
        
        .image-upload-area:hover {
            border-color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
        }
        
        .image-upload-area i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        .image-upload-area p {
            margin-bottom: 0;
            font-weight: 500;
        }
        
        .image-upload-area .text-muted {
            font-size: 0.85rem;
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
        
        /* Status Badge */
        .status-badge {
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
        }
        
        .status-active {
            background-color: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .status-inactive {
            background-color: rgba(239, 68, 68, 0.15);
            color: #ef4444;
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
            
            .current-image {
                max-width: 150px;
                max-height: 150px;
            }
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
                            <h2><i class="bi bi-pencil-square"></i>Edit Deal</h2>
                            <p>Update the information for this deal</p>
                        </div>
                        <div class="card-body">
                            <form method="post" enctype="multipart/form-data" id="editDealForm">
                                <!-- Basic Information Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-info-circle"></i> Basic Information</h3>
                                    <div class="row mb-4">
                                        <div class="col-md-8">
                                            <label class="form-label"><i class="bi bi-tag"></i> Deal Title</label>
                                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($deal['title']) ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><i class="bi bi-toggle-on"></i> Status</label>
                                            <select name="status" class="form-select" required>
                                                <option value="active" <?= $deal['status']=='active'?'selected':'' ?>>Active</option>
                                                <option value="inactive" <?= $deal['status']=='inactive'?'selected':'' ?>>Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label"><i class="bi bi-card-text"></i> Description</label>
                                        <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($deal['description']) ?></textarea>
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
                                                <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($deal['price']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-currency-exchange"></i> Original Price</label>
                                            <div class="price-input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="old_price" class="form-control" value="<?= htmlspecialchars($deal['old_price']) ?>" required>
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
                                            <input type="number" name="sold" class="form-control" value="<?= htmlspecialchars($deal['sold']) ?>" min="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"><i class="bi bi-box-seam"></i> Available Stock</label>
                                            <input type="number" name="available" class="form-control" value="<?= htmlspecialchars($deal['available']) ?>" min="0">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label class="form-label"><i class="bi bi-calendar-event"></i> End Date</label>
                                        <input type="datetime-local" name="end_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($deal['end_date'])) ?>" required>
                                    </div>
                                </div>
                                
                                <!-- Image Section -->
                                <div class="form-section">
                                    <h3 class="section-title"><i class="bi bi-image"></i> Deal Image</h3>
                                    <div class="mb-4">
                                        <label class="form-label"><i class="bi bi-image"></i> Current Image</label>
                                        <div class="image-preview-container">
                                            <?php if (!empty($deal['image'])) { ?>
                                                <img src="../../images/deals/<?= htmlspecialchars($deal['image']) ?>" alt="Deal Image" class="current-image">
                                                <span class="image-label">Current</span>
                                            <?php } else { ?>
                                                <div class="text-center p-4 bg-light rounded">
                                                    <i class="bi bi-image" style="font-size: 3rem; color: #adb5bd;"></i>
                                                    <p class="mt-2 text-muted">No image uploaded</p>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        
                                        <label class="form-label"><i class="bi bi-upload"></i> Upload New Image (Optional)</label>
                                        <div class="image-upload-area" onclick="document.getElementById('imageUpload').click()">
                                            <i class="bi bi-cloud-upload"></i>
                                            <p>Click to upload new image</p>
                                            <p class="text-muted">Leave blank to keep current image</p>
                                            <input type="file" name="image" id="imageUpload" class="d-none" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                    <a href="view-deals.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Deals
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg"></i> Update Deal
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
                    
                    // Update the upload area with preview
                    uploadArea.innerHTML = `
                        <img src="${e.target.result}" class="img-fluid mb-2" style="max-height: 150px; border-radius: 8px;">
                        <p class="mb-0"><strong>${file.name}</strong></p>
                        <p class="text-muted small mb-0">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                        <input type="file" name="image" id="imageUpload" class="d-none" accept="image/*">
                    `;
                    
                    // Re-attach event listener to the new file input
                    document.getElementById('imageUpload').addEventListener('change', arguments.callee);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>