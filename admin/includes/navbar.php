<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Slaymart Admin Panel</title>
  <!-- Favicon -->
  <link rel="shortcut icon" href="../images/logo/favicon.ico" type="image/x-icon">
  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Sweet Alert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
      --sidebar: #4e73df;
      --sidebar-dark: #2e59d9;
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
      --hover-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
      --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      --gradient-success: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
      --gradient-info: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
      --gradient-warning: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: #f0f2f5;
      color: var(--dark);
      overflow-x: hidden;
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar Styles */
    .sidebar {
      width: 250px;
      background: linear-gradient(180deg, var(--sidebar) 10%, var(--sidebar-dark) 100%);
      color: white;
      transition: all 0.3s;
      position: fixed;
      height: 100vh;
      z-index: 100;
      overflow-y: auto;
      box-shadow: var(--card-shadow);
    }

    .sidebar-header {
      padding: 1.5rem;
      text-align: center;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .sidebar-header .logo {
      width: 60px;
      height: 60px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .sidebar-header .logo i {
      font-size: 1.8rem;
      color: white;
    }

    .sidebar-header h3 {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.3rem;
    }

    .sidebar-header p {
      font-size: 0.85rem;
      opacity: 0.8;
    }

    .sidebar-menu {
      padding: 1rem 0;
    }

    .menu-category {
      position: relative;
    }

    .menu-title {
      display: flex;
      align-items: center;
      padding: 0.75rem 1.5rem;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s;
      border-radius: 8px;
      margin: 0.25rem 0.75rem;
    }

    .menu-title:hover {
      color: white;
      background-color: rgba(255, 255, 255, 0.15);
    }

    .menu-title i {
      margin-right: 0.75rem;
      width: 20px;
      text-align: center;
      font-size: 1.1rem;
    }

    .menu-title.active {
      color: white;
      background-color: rgba(255, 255, 255, 0.2);
      border-left: 4px solid white;
    }

    .dropdown-list {
      background-color: rgba(0, 0, 0, 0.1);
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
      border-radius: 0 0 8px 8px;
    }

    .menu-category:hover .dropdown-list,
    .menu-category.active .dropdown-list {
      max-height: 500px;
    }

    .dropdown-item a {
      display: block;
      padding: 0.5rem 1.5rem 0.5rem 3.5rem;
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      font-size: 0.9rem;
      transition: all 0.3s;
    }

    .dropdown-item a:hover {
      color: white;
      background-color: rgba(255, 255, 255, 0.1);
    }

    /* Main Content Styles */
    .main-content {
      margin-left: 250px;
      flex: 1;
      display: flex;
      flex-direction: column;
      transition: margin-left 0.3s ease;
    }

    /* Header Styles */
    .header-top {
      background-color: white;
      padding: 0.75rem 0;
      box-shadow: var(--card-shadow);
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1.5rem;
    }

    .header-top-actions {
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    .header-social-container {
      display: flex;
      align-items: center;
      list-style: none;
    }

    .header-social-container span {
      margin-right: 1rem;
      font-weight: 500;
      color: var(--dark);
    }

    .social-link {
      color: var(--primary);
      text-decoration: none;
      font-weight: 500;
      padding: 0.5rem 1rem;
      border-radius: 4px;
      transition: all 0.3s;
    }

    .social-link:hover {
      background-color: rgba(78, 115, 223, 0.1);
    }

    /* Topbar Styles */
    .topbar {
      background-color: white;
      padding: 1rem 1.5rem;
      box-shadow: var(--card-shadow);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }

    .topbar-left {
      display: flex;
      align-items: center;
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--dark);
      cursor: pointer;
      margin-right: 1rem;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .menu-toggle:hover {
      background-color: #f0f2f5;
    }

    .search-bar {
      position: relative;
    }

    .search-bar input {
      padding: 0.5rem 1rem 0.5rem 2.5rem;
      border-radius: 2rem;
      border: 1px solid #e3e6f0;
      width: 250px;
      transition: all 0.3s;
      background-color: #f8f9fc;
    }

    .search-bar input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
      width: 300px;
      background-color: white;
    }

    .search-bar i {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--secondary);
    }

    .topbar-icon {
      position: relative;
      margin-left: 1rem;
      color: var(--secondary);
      font-size: 1.2rem;
      cursor: pointer;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s;
    }

    .topbar-icon:hover {
      background-color: #f0f2f5;
      color: var(--primary);
    }

    .topbar-icon .badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: var(--danger);
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
    }

    .user-profile {
      display: flex;
      align-items: center;
      margin-left: 1.5rem;
      cursor: pointer;
      padding: 0.5rem;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .user-profile:hover {
      background-color: #f0f2f5;
    }

    .user-profile img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      margin-right: 0.5rem;
      border: 2px solid var(--primary);
    }

    .user-profile span {
      font-weight: 500;
      color: var(--dark);
    }

    /* Dashboard Content */
    .dashboard-content {
      padding: 1.5rem;
      flex: 1;
    }

    .dashboard-header {
      margin-bottom: 1.5rem;
    }

    .dashboard-header h1 {
      font-size: 1.75rem;
      color: var(--dark);
      margin-bottom: 0.5rem;
      font-weight: 700;
    }

    .dashboard-header p {
      color: var(--secondary);
    }

    /* Stats Cards */
    .stats-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background-color: white;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
      padding: 1.5rem;
      display: flex;
      align-items: center;
      transition: all 0.3s;
      overflow: hidden;
      position: relative;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      opacity: 0.1;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }

    .stat-card.primary::before {
      background: var(--gradient-primary);
    }

    .stat-card.success::before {
      background: var(--gradient-success);
    }

    .stat-card.info::before {
      background: var(--gradient-info);
    }

    .stat-card.warning::before {
      background: var(--gradient-warning);
    }

    .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      font-size: 1.5rem;
      color: white;
      z-index: 1;
    }

    .stat-icon.primary {
      background: var(--gradient-primary);
    }

    .stat-icon.success {
      background: var(--gradient-success);
    }

    .stat-icon.info {
      background: var(--gradient-info);
    }

    .stat-icon.warning {
      background: var(--gradient-warning);
    }

    .stat-info h3 {
      font-size: 1.5rem;
      margin-bottom: 0.25rem;
      color: var(--dark);
      font-weight: 700;
    }

    .stat-info p {
      color: var(--secondary);
      font-size: 0.9rem;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #e3e6f0;
    }

    .section-header h2 {
      font-size: 1.25rem;
      color: var(--dark);
      font-weight: 600;
    }

    .section-header a {
      color: var(--primary);
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: all 0.3s;
    }

    .section-header a:hover {
      color: var(--info);
    }

    .activity-list {
      list-style: none;
    }

    .activity-item {
      display: flex;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid #e3e6f0;
      transition: all 0.3s;
    }

    .activity-item:last-child {
      border-bottom: none;
    }

    .activity-item:hover {
      background-color: #f8f9fc;
      border-radius: 0.5rem;
      padding-left: 0.5rem;
      padding-right: 0.5rem;
    }

    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
      color: white;
      font-size: 1rem;
    }

    .activity-icon.primary {
      background: var(--gradient-primary);
    }

    .activity-icon.success {
      background: var(--gradient-success);
    }

    .activity-icon.warning {
      background: var(--gradient-warning);
    }

    .activity-details {
      flex: 1;
    }

    .activity-details h4 {
      font-size: 0.95rem;
      margin-bottom: 0.25rem;
      color: var(--dark);
      font-weight: 600;
    }

    .activity-details p {
      font-size: 0.8rem;
      color: var(--secondary);
    }

    .activity-time {
      font-size: 0.8rem;
      color: var(--secondary);
      white-space: nowrap;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    .quick-action-card {
      background: white;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
    }

    .quick-action-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--hover-shadow);
    }

    .quick-action-icon {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 1.5rem;
      color: white;
    }

    .quick-action-icon.primary {
      background: var(--gradient-primary);
    }

    .quick-action-icon.success {
      background: var(--gradient-success);
    }

    .quick-action-icon.info {
      background: var(--gradient-info);
    }

    .quick-action-icon.warning {
      background: var(--gradient-warning);
    }

    .quick-action-card h3 {
      font-size: 1.1rem;
      margin-bottom: 0.5rem;
      color: var(--dark);
    }

    .quick-action-card p {
      font-size: 0.85rem;
      color: var(--secondary);
    }

    /* Responsive Design */
    @media (min-width: 769px) {
      .sidebar.collapsed {
        width: 70px;
      }

      .sidebar.collapsed .sidebar-header h3,
      .sidebar.collapsed .sidebar-header p,
      .sidebar.collapsed .menu-title span,
      .sidebar.collapsed .user-profile span {
        display: none;
      }

      .sidebar.collapsed .menu-title {
        justify-content: center;
        padding: 0.75rem;
      }

      .sidebar.collapsed .menu-title i {
        margin-right: 0;
      }

      .sidebar.collapsed .dropdown-list {
        position: absolute;
        left: 70px;
        top: 0;
        width: 200px;
        background-color: var(--sidebar-dark);
        box-shadow: var(--card-shadow);
        z-index: 10;
      }

      .sidebar.collapsed .dropdown-item a {
        padding: 0.5rem 1rem;
      }

      .main-content.expanded {
        margin-left: 70px;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .menu-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .stats-container {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
      }

      .stat-card {
        padding: 1rem;
      }

      .stat-icon {
        width: 50px;
        height: 50px;
        margin-right: 0.75rem;
      }

      .stat-info h3 {
        font-size: 1.1rem;
      }
    }

    @media (max-width: 576px) {
      .dashboard-content {
        padding: 1rem;
      }

      .dashboard-header h1 {
        font-size: 1.5rem;
      }

      .stats-container {
        grid-template-columns: 1fr;
      }

      .topbar {
        padding: 0.75rem;
      }

      .search-bar {
        width: 100%;
      }

      .search-bar input {
        width: 100%;
      }

      .search-bar input:focus {
        width: 100%;
      }

      .user-profile span {
        display: none;
      }

      .section-header {
        flex-direction: column;
        align-items: flex-start;
      }

      .quick-actions {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <!-- Dashboard Container -->
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <i class="fas fa-shopping-bag"></i>
        </div>
        <h3>Slaymart</h3>
        <p>Admin Dashboard</p>
      </div>
      <div class="sidebar-menu">
        <ul class="desktop-menu-category-list">
          <li class="menu-category">
            <a href="#" class="menu-title active">
              <i class="fas fa-tachometer-alt"></i>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-home"></i>
              <span>Home</span>
            </a>
          </li>
          <!-- Users Menu -->
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-users"></i>
              <span>Users</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="./users/users_manage.php">Users Info</a>
              </li>
            </ul>
          </li>
          <!-- Orders Menu -->
          <li class="menu-category">
            <a href="./orders/customer_orders.php" class="menu-title">
              <i class="fas fa-shopping-cart"></i>
              <span>Customers Orders</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="./orders/customer_orders.php">Customer Orders</a>
              </li>
            </ul>
          </li>
          <!-- Products Menu -->
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-box"></i>
              <span>Product</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="./product/add_product.php">Add Product</a>
              </li>
               <li class="dropdown-item">
                <a href="./product/add_colors_sizes.php">Add Color & Sizes</a>
              </li>
              <li class="dropdown-item">
                <a href="./product/manage_product.php">Product Management</a>
              </li>
            </ul>
          </li>
          <!-- Transactions Menu -->
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-credit-card"></i>
              <span>All Transactions</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="../admin/transactions/admin_transactions.php">History</a>
              </li>
            </ul>
          </li>
          <!-- Banner Menu -->
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-image"></i>
              <span>Banner</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="web-banner/add-banner.php">Add Banner</a>
              </li>
              <li class="dropdown-item">
                <a href="web-banner/view-banners.php">View Banner</a>
              </li>
            </ul>
          </li>
          <!-- Deal of the Day Menu -->
          <li class="menu-category">
            <a href="#" class="menu-title">
              <i class="fas fa-tags"></i>
              <span>Deal of the Day</span>
            </a>
            <ul class="dropdown-list">
              <li class="dropdown-item">
                <a href="deal/add-deal.php">Add Deal</a>
              </li>
              <li class="dropdown-item">
                <a href="deal/view-deals.php">Manage Deal</a>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
      <?php
      // Agar admin login nahi hai to redirect
      if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: users/admin_login.php");
        exit;
      }
      $username = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
      ?>
      <header>
        <div class="header-top">
          <div class="container">
            <div class="header-top-actions">
              <ul class="header-social-container">
                <span class="navbar-text me-3">
                  Welcome, <?= htmlspecialchars($username) ?>
                </span>
                <a href="./users/logout.php" class="social-link">Logout</a>
              </ul>
            </div>
          </div>
        </div>
      </header>
      <!-- Topbar -->
      <div class="topbar">
        <div class="container">
          <div class="topbar-left">
            <!-- Added ID to the menu toggle button -->
            <button class="menu-toggle" id="menuToggle">
              <i class="fas fa-bars"></i>
            </button>
            <div class="search-bar">
              <i class="fas fa-search"></i>
              <input type="text" placeholder="Search...">
            </div>
          </div>
        </div>
      </div>
      <!-- Dashboard Content -->
      <div class="dashboard-content">
        <div class="dashboard-header">
          <h1>Dashboard</h1>
          <p>Welcome to our Slaymart Store admin panel</p>
        </div>

        <!-- Carts -->
        <?php
        // 1. Total Earnings
        $earningsQuery = "SELECT SUM(price * quantity) AS total_earnings FROM orders WHERE status IN ('confirmed','processing','shipped','delivered')";
        $earningsResult = mysqli_query($conn, $earningsQuery);
        $earnings = mysqli_fetch_assoc($earningsResult)['total_earnings'] ?? 0;

        // 2. Total Orders
        $ordersQuery = "SELECT COUNT(*) AS total_orders FROM orders";
        $ordersResult = mysqli_query($conn, $ordersQuery);
        $totalOrders = mysqli_fetch_assoc($ordersResult)['total_orders'] ?? 0;

        // 3. Total Customers
        $customersQuery = "SELECT COUNT(DISTINCT user_id) AS total_customers FROM orders";
        $customersResult = mysqli_query($conn, $customersQuery);
        $totalCustomers = mysqli_fetch_assoc($customersResult)['total_customers'] ?? 0;

        // 4. Total Products
        $productsQuery = "SELECT COUNT(*) AS total_products FROM products";
        $productsResult = mysqli_query($conn, $productsQuery);
        $totalProducts = mysqli_fetch_assoc($productsResult)['total_products'] ?? 0;
        ?>

        <!-- Carts -->
        <div class="stats-container">
          <div class="stat-card success">
            <div class="stat-icon success">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
              <h3><?= $totalOrders ?></h3>
              <p>Orders</p>
            </div>
          </div>
          <div class="stat-card info">
            <div class="stat-icon info">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-info">
              <h3><?= $totalCustomers ?></h3>
              <p>Customers</p>
            </div>
          </div>
          <div class="stat-card warning">
            <div class="stat-icon warning">
              <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-info">
              <h3><?= $totalProducts ?></h3>
              <p>Products</p>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <div class="quick-action-card">
            <div class="quick-action-icon primary">
              <a href="./product/add_product.php" style="color:white;"><i class="fas fa-plus-circle"></i></a>
            </div>
            <h3>Add Product</h3>
            <p>Create a new product listing</p>
          </div>
          <div class="quick-action-card">
            <div class="quick-action-icon success">
             <a href="./deal/add-deal.php" style="color:white;"><i class="fas fa-tags"></i></a>
            </div>
            <h3>Add Deal</h3>
            <p>Create a new deal of the day</p>
          </div>
          <div class="quick-action-card">
            <div class="quick-action-icon info">
              <a href="./web-banner/add-banner.php" style="color:white;"><i class="fas fa-image"></i></a>
            </div>
            <h3>Add Banner</h3>
            <p>Create a new banner for homepage</p>
          </div>
        </div>

      </div>
    </div>
  </div>
  <script>
    // Fixed: Check if menuToggle exists before adding event listener
    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
      menuToggle.addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        // For mobile devices
        if (window.innerWidth <= 768) {
          sidebar.classList.toggle('active');
        } else {
          // For desktop/tablet devices
          sidebar.classList.toggle('collapsed');
          mainContent.classList.toggle('expanded');
        }
      });
    }

    // Handle dropdown menus
    document.querySelectorAll('.menu-category').forEach(item => {
      item.addEventListener('click', function() {
        // Close other dropdowns
        document.querySelectorAll('.menu-category').forEach(cat => {
          if (cat !== this) {
            cat.classList.remove('active');
          }
        });
        // Toggle current dropdown
        this.classList.toggle('active');
      });
    });

    // Handle active menu item
    document.querySelectorAll('.menu-title').forEach(item => {
      item.addEventListener('click', function(e) {
        if (!this.nextElementSibling || !this.nextElementSibling.classList.contains('dropdown-list')) {
          e.preventDefault();
          // Remove active class from all menu items
          document.querySelectorAll('.menu-title').forEach(menuItem => {
            menuItem.classList.remove('active');
          });
          // Add active class to clicked item
          this.classList.add('active');
        }
      });
    });

    // Fixed: Check if logout link exists before adding event listener
    const logoutLink = document.querySelector('.social-link');
    if (logoutLink) {
      logoutLink.addEventListener('click', function(e) {
        e.preventDefault();
        Swal.fire({
          title: 'Are you sure?',
          text: "You will be logged out of your account",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#4e73df',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, logout!'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = this.getAttribute('href');
          }
        });
      });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.getElementById('menuToggle');

      if (window.innerWidth <= 768 && sidebar && menuToggle) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
          sidebar.classList.remove('active');
        }
      }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      const sidebar = document.getElementById('sidebar');
      const mainContent = document.getElementById('mainContent');

      if (window.innerWidth > 768) {
        // Reset mobile state when resizing to desktop
        if (sidebar) {
          sidebar.classList.remove('active');
        }
        // If sidebar was collapsed, maintain that state
        if (sidebar && mainContent) {
          if (sidebar.classList.contains('collapsed')) {
            mainContent.classList.add('expanded');
          } else {
            mainContent.classList.remove('expanded');
          }
        }
      } else {
        // Reset desktop state when resizing to mobile
        if (mainContent) {
          mainContent.classList.remove('expanded');
        }
      }
    });
  </script>
</body>

</html>