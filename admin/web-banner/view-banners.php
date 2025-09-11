<?php
include("../../config/db.php");
include "../includes/session_check.php";
$query = "SELECT * FROM banners";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Management</title>
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
            --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            --hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: var(--dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            color: white;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: white;
            color: var(--primary);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        .search-box {
            position: relative;
            width: 350px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: 1px solid #e9ecef;
            border-radius: 30px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s ease;
            background-color: #f8f9fe;
        }
        
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(78, 114, 228, 0.1);
            background-color: white;
        }
        
        .search-box i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary);
        }
        
        .filter-options {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 8px 15px;
            background-color: #f8f9fe;
            border: 1px solid #e9ecef;
            border-radius: 20px;
            color: var(--secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .table-action-btn {
            padding: 8px 12px;
            background-color: #f8f9fe;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            color: var(--secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .table-action-btn:hover {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
        }
        
        thead {
            background-color: #f8f9fe;
        }
        
        th {
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        tbody tr {
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        tbody tr:hover {
            background-color: rgba(78, 114, 228, 0.05);
        }
        
        .banner-img {
            width: 100px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .banner-img:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .banner-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 4px;
        }
        
        .banner-subtitle {
            color: var(--secondary);
            font-size: 14px;
        }
        
        .banner-price {
            font-weight: 700;
            color: var(--success);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .edit-btn {
            background-color: var(--info);
            color: white;
        }
        
        .edit-btn:hover {
            background-color: #0ea2c0;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(17, 205, 239, 0.3);
        }
        
        .delete-btn {
            background-color: var(--danger);
            color: white;
        }
        
        .delete-btn:hover {
            background-color: #ec0c38;
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(245, 54, 92, 0.3);
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .pagination button {
            background-color: white;
            border: 1px solid #e9ecef;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--secondary);
            font-weight: 500;
        }
        
        .pagination button:hover, .pagination button.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--secondary);
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            color: var(--primary);
            opacity: 0.7;
        }
        
        .empty-state h3 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
        }
        
        .empty-state p {
            max-width: 400px;
            margin: 0 auto 25px;
        }
        
        @media (max-width: 992px) {
            .search-box {
                width: 280px;
            }
        }
        
        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .header-actions {
                width: 100%;
                justify-content: center;
            }
            
            .search-box {
                width: 100%;
            }
            
            .filter-options {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .banner-img {
                width: 80px;
                height: 60px;
            }
            
            th, td {
                padding: 12px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
        
        @media (max-width: 576px) {
            .table-container {
                overflow-x: auto;
            }
            
            table {
                width: 600px;
            }
            
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1 class="page-title"><i class="fas fa-images"></i> Banner Management</h1>
            <div class="header-actions">
                <a href="../index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="add-banner.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Banner
                </a>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <div class="table-title" style="text-align:center;">Banner List</div>
            </div>
            <?php if(mysqli_num_rows($result) > 0) { ?>
                <table id="bannersTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Subtitle</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            // Reset the result pointer to the beginning
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)) { 
                        ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td>
                                    <img src="../../images/banners/<?php echo $row['image']; ?>" 
                                         alt="Banner Image" 
                                         class="banner-img">
                                </td>
                                <td>
                                    <div class="banner-title"><?php echo $row['title']; ?></div>
                                </td>
                                <td>
                                    <div class="banner-subtitle"><?php echo $row['subtitle']; ?></div>
                                </td>
                                <td>
                                    <div class="banner-price"><i class="fas fa-dollar-sign"></i><?php echo $row['price']; ?></div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit-banner.php?id=<?php echo $row['id']; ?>" class="action-btn edit-btn">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="delete-banner.php?id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this banner? This action cannot be undone.')">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="empty-state">
                    <i class="fas fa-images"></i>
                    <h3>No Banners Found</h3>
                    <p>It looks like you don't have any banners yet. Start by adding a new banner to the system.</p>
                    <a href="add-banner.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add Your First Banner
                    </a>
                </div>
            <?php } ?>
        </div>

        <div class="pagination">
            <button><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button>2</button>
            <button>3</button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <script>
        // Simple search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let searchValue = this.value.toLowerCase();
            let tableRows = document.querySelectorAll('#bannersTable tbody tr');
            
            tableRows.forEach(row => {
                let text = row.textContent.toLowerCase();
                if(text.includes(searchValue)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter buttons functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you would implement the actual filtering logic
                // For now, it's just a visual change
            });
        });

        // Table action buttons
        document.querySelectorAll('.table-action-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Add ripple effect
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 200);
            });
        });
    </script>
</body>
</html>