<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Policy - Admin Panel</title>
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
        
        /* Content Card */
        .content-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .content-card:hover {
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
        
        .card-body {
            padding: 2.5rem;
        }
        
        /* Policy Sections */
        .policy-section {
            margin-bottom: 2.5rem;
        }
        
        .policy-section:last-child {
            margin-bottom: 0;
        }
        
        .section-title {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.4rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 0.5rem;
        }
        
        .policy-content {
            line-height: 1.7;
            color: #4b5563;
        }
        
        .policy-content p {
            margin-bottom: 1rem;
        }
        
        .policy-content ul {
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .policy-content li {
            margin-bottom: 0.5rem;
        }
        
        .highlight-box {
            background-color: rgba(67, 97, 238, 0.05);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }
        
        .highlight-box h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        
        /* Shipping Timeline */
        .timeline {
            position: relative;
            padding: 2rem 0;
            margin: 2rem 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background-color: var(--border-color);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 2rem;
            margin-bottom: 2rem;
        }
        
        .timeline-item:last-child {
            margin-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: var(--primary-color);
            border: 4px solid white;
            box-shadow: 0 0 0 2px var(--border-color);
        }
        
        .timeline-title {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }
        
        .timeline-description {
            color: #6b7280;
            line-height: 1.5;
        }
        
        /* Info Cards */
        .info-card {
            background-color: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .info-card-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .info-card-title {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.75rem;
        }
        
        .info-card-text {
            color: #6b7280;
            line-height: 1.5;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
            
            .timeline::before {
                left: 15px;
            }
            
            .timeline-item {
                padding-left: 3rem;
            }
            
            .timeline-item::before {
                left: 7px;
            }
        }
        
        /* Footer */
        footer {
            background-color: white;
            border-top: 1px solid var(--border-color);
            padding: 1.5rem 0;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Shipping Policy Card -->
                    <div class="card content-card">
                        <div class="card-header">
                            <h2><i class="bi bi-truck"></i>Shipping Policy</h2>
                        </div>
                        <div class="card-body">
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-info-circle"></i>Overview</h3>
                                <div class="policy-content">
                                    <p>At Slaymart, we're committed to providing you with a seamless and efficient shipping experience. We understand that receiving your orders in a timely manner is important to you, and we strive to meet your expectations every time.</p>
                                    <p>Our standard shipping time is <strong>4-5 business days</strong> from the date of order confirmation. We work with trusted shipping partners to ensure your products reach you safely and on time.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-clock-history"></i>Shipping Timeline</h3>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <h4 class="timeline-title">Order Confirmation</h4>
                                        <p class="timeline-description">Once your order is confirmed, you'll receive an email with your order details and estimated delivery date.</p>
                                    </div>
                                    <div class="timeline-item">
                                        <h4 class="timeline-title">Order Processing (1-2 days)</h4>
                                        <p class="timeline-description">Our team carefully processes your order, packs it with care, and prepares it for shipping.</p>
                                    </div>
                                    <div class="timeline-item">
                                        <h4 class="timeline-title">Shipping (2-3 days)</h4>
                                        <p class="timeline-description">Your order is handed over to our shipping partner and is on its way to your delivery address.</p>
                                    </div>
                                    <div class="timeline-item">
                                        <h4 class="timeline-title">Delivery</h4>
                                        <p class="timeline-description">Your order arrives at your doorstep within the promised 4-5 business days timeframe.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-geo-alt"></i>Shipping Areas</h3>
                                <div class="policy-content">
                                    <p>We currently ship to the following areas:</p>
                                    <ul>
                                        <li>All major cities and metropolitan areas</li>
                                        <li>Most suburban and rural areas</li>
                                    </ul>
                                    <p>If you're unsure whether we ship to your area, please contact our customer service team for confirmation before placing your order.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-currency-dollar"></i>Shipping Costs</h3>
                                <div class="policy-content">
                                    <p>We offer the following shipping options:</p>
                                    <ul>
                                        <li><strong>Standard Shipping (4-5 days):</strong> Free on orders over 250 PKR, otherwise $5.99</li>
                                        <li><strong>Overnight Shipping (1 day):</strong> 500 PKR (available in select areas only)</li>
                                    </ul>
                                    <p>Shipping costs are calculated at checkout based on your location and the weight of your order.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-exclamation-triangle"></i>Important Information</h3>
                                <div class="policy-content">
                                    <div class="highlight-box">
                                        <h4>Delivery Timeframes</h4>
                                        <p>Please note that delivery timeframes are estimates and not guarantees. While we make every effort to deliver your orders within the promised timeframe, occasional delays may occur due to unforeseen circumstances such as weather conditions, holidays, or other factors beyond our control.</p>
                                    </div>
                                    
                                    <div class="highlight-box">
                                        <h4>Delivery Attempts</h4>
                                        <p>If you're not available to receive your delivery, our shipping partner will make up to two additional delivery attempts. After three unsuccessful attempts, your order will be returned to our warehouse, and you may be subject to additional shipping fees if you request redelivery. Please note, if you repeatedly fail to receive your orders, your account may be suspended or permanently deleted.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-question-circle"></i>Frequently Asked Questions</h3>
                                <div class="policy-content">
                                    <div class="info-card">
                                        <div class="info-card-icon">
                                            <i class="bi bi-calendar-check"></i>
                                        </div>
                                        <h4 class="info-card-title">Can I change my delivery address after placing an order?</h4>
                                        <p class="info-card-text">If you need to update your delivery address, please contact us directly through our official phone number or email provided on our website. Once we verify your request, your order details will be updated and confirmed accordingly.</p>
                                    </div>
                                    
                                    <div class="info-card">
                                        <div class="info-card-icon">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <h4 class="info-card-title">What if my order arrives damaged?</h4>
                                        <p class="info-card-text">If your order arrives damaged, please contact our customer service team within 48 hours of delivery. We'll arrange for a replacement or refund as per our return policy.</p>
                                    </div>
                                    
                                    <div class="info-card">
                                        <div class="info-card-icon">
                                            <i class="bi bi-person-check"></i>
                                        </div>
                                        <h4 class="info-card-title">Do I need to be present to receive my delivery?</h4>
                                        <p class="info-card-text">While it's not always necessary to be present, we recommend it to ensure the security of your package. If you won't be available, consider having the package delivered to a secure location or to a trusted neighbor.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-envelope"></i>Contact Us</h3>
                                <div class="policy-content">
                                    <p>If you have any questions about our shipping policy or need assistance with your order, please don't hesitate to contact us:</p>
                                    <ul>
                                        <li><strong>Email:</strong> kaifsheikh126@gmail.com</li>
                                        <li><strong>Phone:</strong> +92 (310) 8422790</li>
                                        <li><strong>Live Chat:</strong> Available on our website during business hours</li>
                                    </ul>
                                    <p>Our customer service team is available Monday to Friday, 9 AM to 8 PM, and Saturday to Sunday, 10 AM to 6 PM.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-0 text-muted">&copy; <?= date('Y') ?> Welcome Slaymart Questions. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>