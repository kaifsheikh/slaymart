<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SlayMart Store – Your one-stop online shop for electronics, kitchen essentials, home décor, fitness gear, and more. Quality products at affordable prices with reliable service." />
  <meta name="keywords" content="SlayMart, online store, electronics, kitchen, fitness, home shopping" />
<meta name="author" content="Muhammad Kaif" />
  <title>SlayMart Store</title>
 
  <!-- favicon -->
  <link rel="shortcut icon" href="./images/logo/favicon.png" type="image/x-icon">
  <!-- custom css link -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <!-- google font link -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">
  <!-- Sweet Alert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- AOS Animation CDN -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>

  <style>
    /* Mobile Login/Signup Buttons Style */
    .login-mobile {
      display: none;
    }

    @media (max-width: 768px) {
      .login-mobile {
        display: inline-block;
        margin: 6px 4px;
        padding: 6px 14px;
        /* smaller padding */
        background: linear-gradient(135deg, #ff7e5f, #ff6b81);
        color: #fff;
        font-size: 13px;
        /* smaller font */
        font-weight: 600;
        border-radius: 22px;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.25s ease-in-out;
      }

      /* Alternate style for Sign In */
      .login-mobile.alt {
        background: #fff;
        color: #ff6b81;
        border: 1.5px solid #ff6b81;
      }

      /* Hover Effect */
      .login-mobile:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.18);
      }

      .login-mobile.alt:hover {
        background: #ff6b81;
        color: #fff;
      }

      /* Active (Click Effect) */
      .login-mobile:active {
        transform: translateY(0);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
      }
    }

    /* Mobile Login/Signup Buttons Style */

    /* Logout Button Css Mobile Device */
    .mobile-bottom-navigation .logout-btn {
      background: linear-gradient(135deg, #ff5f6d, #ff3c3c);
      color: #fff !important;
      padding: 8px 16px;
      border-radius: 30px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 4px 12px rgba(255, 60, 60, 0.3);
      transition: 0.3s ease;
      font-size: 14px;
    }

    /* Hover Effect (only logout btn) */
    .mobile-bottom-navigation .logout-btn:hover {
      background: linear-gradient(135deg, #ff3c3c, #ff1a1a);
      box-shadow: 0 6px 15px rgba(255, 60, 60, 0.45);
      transform: translateY(-2px);
    }

    /* Login Register Desktop Widht */
    .header-auth-container {
      list-style: none;
      margin: 0;
      padding: 0;
      display: flex;
      align-items: center;
      gap: 12px;
      font-family: 'Segoe UI', Tahoma, sans-serif;
    }

    /* Welcome text */
    .welcome-text {
      font-size: 15px;
      font-weight: 500;
      color: #444;
      margin-right: 8px;
      white-space: nowrap;
    }

    /* Common Button Styles */
    .auth-btn {
      padding: 8px 20px;
      border-radius: 50px;
      font-size: 14px;
      font-weight: 600;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }

    /* Hover shine effect */
    .auth-btn::after {
      content: "";
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.3);
      transform: skewX(-25deg);
      transition: 0.5s;
    }

    .auth-btn:hover::after {
      left: 120%;
    }

    /* Login Button */
    .login-btn {
      background: #f5f5f5;
      border: 1px solid #ccc;
      color: #333;
    }

    .login-btn:hover {
      background: #e9e9e9;
      border-color: #bbb;
    }

    /* Register Button (highlight) */
    .register-btn {
      background: linear-gradient(135deg, #4CAF50, #2e7d32);
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(76, 175, 80, 0.35);
    }

    .register-btn:hover {
      background: linear-gradient(135deg, #43a047, #1b5e20);
      box-shadow: 0 6px 18px rgba(76, 175, 80, 0.45);
      transform: translateY(-2px);
    }

    /* Logout Button */
    .logout-btn {
      background: linear-gradient(135deg, #ff5f6d, #ff3c3c);
      color: #fff !important;
      box-shadow: 0 4px 12px rgba(255, 60, 60, 0.35);
    }

    .logout-btn:hover {
      background: linear-gradient(135deg, #ff3c3c, #ff1a1a);
      box-shadow: 0 6px 18px rgba(255, 60, 60, 0.45);
      transform: translateY(-2px);
    }
    /* Login Register Desktop Widht */
  </style>
</head>

<body>

  <!-- HEADER -->
  <header data-aos="fade-up" data-aos-duration="500">

    <!-- Desktop Width Section 1 -->
    <div class="header-top">
      <div class="container">


        <!-- Desktop Width -->
        <ul class="header-social-container">
          <li>
            <a href="https://www.instagram.com/slaymartt_/" target="_blank" class="social-link">
              <ion-icon name="logo-instagram"></ion-icon>
            </a>
          </li>
        </ul>


        <!-- Desktop Width -->
        <div class="header-alert-news">
          <p>
            <b>Free delivery</b>
            on orders above 3,000 PKR
          </p>
        </div>


        <!-- Desktop Login/Register or Logout -->
        <div class="header-top-actions">
          <ul class="header-auth-container">

            <?php if (isset($_SESSION['user_id'])) : ?>
              <!-- Logged In -->
              <span class="welcome-text">👋 Welcome, <b><?= $_SESSION['user_name']; ?></b></span>
              <a href="./users/logout.php" class="auth-btn logout-btn">Logout</a>

            <?php else : ?>
              <!-- Not Logged In -->
              <a href="./users/login.php" class="auth-btn login-btn">Login</a>
              <a href="./users/register.php" class="auth-btn register-btn">Register</a>
            <?php endif; ?>

          </ul>
        </div>
      </div>
    </div>


    <!-- Desktop Widht Section 2 -->
    <div class="header-main">

      <div class="container">

        <a href="./index.php" class="header-logo">
          <img src="./images/logo/logo.png" alt="Anon's logo" width="100">
        </a>

        <!-- Search Functionality -->
        <div class="header-search-container">
          <form action="./search/search.php" method="GET">
            <input type="search" name="search" class="search-field" placeholder="Enter your product name...">
            <button type="submit" class="search-btn">
              <ion-icon name="search-outline"></ion-icon>
            </button>
          </form>
        </div>


        <!-- Login and Register Button Mobile Width Only -->
        <?php
        if (session_status() === PHP_SESSION_NONE) {
          session_start();
        }

        if (!isset($_SESSION['user_id'])):
        ?>

          <!-- Mobile width Register -->
          <a href="./users/register.php" class="banner-btn login-mobile">
            Sign Up
          </a>
          <!-- Mobile width Login -->
          <a href="./users/login.php" class="banner-btn login-mobile alt">
            Sign In
          </a>

        <?php endif; ?>


        <!-- Desktop Widht -->
        <?php
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
          session_start();
        }

        if (isset($_SESSION['user_id'])):
        ?>
          <div class="header-user-actions">

            <!-- Orders Button -->
            <a href="./users/my_orders.php" class="action-btn">
              <ion-icon name="bag-handle-outline"></ion-icon>
            <span class="count">0</span>
            </a>

            <!-- Add to Cart Button -->
            <a href="./add-to-cart/index.php" class="action-btn">
              <ion-icon name="cart-outline"></ion-icon>
            <span class="count">0</span>
            </a>

          </div>
        <?php endif; ?> <!-- If Close here -->
      </div>


      <!-- Mobile Widht Bottom Navbar after Login -->
      <?php
      if (session_status() === PHP_SESSION_NONE) {
        session_start();
      }
      ?>
      <?php if (isset($_SESSION['user_id'])): ?>
        <div class="mobile-bottom-navigation">

          <!-- My Order -->
          <a href="./users/my_orders.php" class="action-btn">
            <ion-icon name="bag-handle-outline"></ion-icon>
            <span class="count">0</span>
          </a>
          
          <!-- My Order -->
          <a href="./users/add-to-cart.php" class="action-btn">
            <ion-icon name="cart-outline"></ion-icon>
            <span class="count">0</span>
          </a>

          <!-- Logout -->
          <a href="./users/logout.php" class="logout-btn">
            <ion-icon name="log-out-outline"></ion-icon>
            <span>Logout</span>
          </a>

        </div>
      <?php endif; ?>
  </header>
