<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Admin Panel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
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
        
        /* Privacy Policy Sections */
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
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Privacy Policy Card -->
                    <div class="card content-card">
                        <div class="card-header">
                            <h2><i class="bi bi-shield-check"></i>Privacy Policy</h2>
                        </div>
                        <div class="card-body">
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-info-circle"></i>Introduction</h3>
                                <div class="policy-content">
                                    <p>Welcome to our Privacy Policy page. Your privacy is critically important to us. This Privacy Policy document outlines the types of personal information that is received and collected by our website and how it is used.</p>
                                    <p>By using our website, you hereby consent to our Privacy Policy and agree to its terms.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-person-badge"></i>Information We Collect</h3>
                                <div class="policy-content">
                                    <p>When you visit our website, we may collect certain information from you, including:</p>
                                    <ul>
                                        <li>Personal identification information (Name, email address, phone number, etc.)</li>
                                        <li>Device information (IP address, browser type, operating system)</li>
                                        <li>Usage data (pages visited, time spent on our site)</li>
                                        <li>Cookies and tracking technologies</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-shield-lock"></i>How We Use Your Information</h3>
                                <div class="policy-content">
                                    <p>We use the information we collect in various ways, including to:</p>
                                    <ul>
                                        <li>Provide, operate, and maintain our website</li>
                                        <li>Improve, personalize, and expand our website</li>
                                        <li>Understand and analyze how you use our website</li>
                                        <li>Develop new products, services, features, and functionality</li>
                                        <li>Communicate with you, either directly or through one of our partners</li>
                                        <li>Process your transactions and manage your orders</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-cookie"></i>Cookies and Tracking</h3>
                                <div class="policy-content">
                                    <p>Our website uses "cookies" to help personalize your experience. A cookie is a file that contains an identifier (a string of letters and numbers) that is sent by a web server to a web browser and stored by the browser.</p>
                                    <p>We use cookies to:</p>
                                    <ul>
                                        <li>Remember and recognize your preferences</li>
                                        <li>Understand how you use our website</li>
                                        <li>Analyze trends and traffic patterns</li>
                                        <li>Provide personalized content and advertisements</li>
                                    </ul>
                                    <div class="highlight-box">
                                        <h4>Your Cookie Choices</h4>
                                        <p>You have the option to either accept or refuse cookies. Most web browsers automatically accept cookies, but you can usually modify your browser setting to decline cookies if you prefer.</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-share"></i>Information Sharing</h3>
                                <div class="policy-content">
                                    <p>We may share your personal information with third parties in the following circumstances:</p>
                                    <ul>
                                        <li>With service providers to monitor and analyze the use of our service</li>
                                        <li>With business partners to offer you certain products, services or promotions</li>
                                        <li>With your consent, we may disclose your personal information for any other purpose</li>
                                        <li>If we are required by law to do so</li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-lock"></i>Data Security</h3>
                                <div class="policy-content">
                                    <p>The security of your data is important to us. We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                                    <p>Despite our efforts, no security measures are perfect or impenetrable. We cannot guarantee the absolute security of your information.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-globe"></i>International Data Transfers</h3>
                                <div class="policy-content">
                                    <p>Your information may be transferred to — and maintained on — computers located outside of your state, province, country or other governmental jurisdiction where the data protection laws may differ from those from your jurisdiction.</p>
                                    <p>If you are located outside [Your Country] and choose to provide information to us, please note that we transfer the data, including Personal Data, to [Your Country] and process it there.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-pencil-square"></i>Changes to This Privacy Policy</h3>
                                <div class="policy-content">
                                    <p>We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>
                                    <p>You are advised to review this Privacy Policy periodically for any changes. Changes to this Privacy Policy are effective when they are posted on this page.</p>
                                </div>
                            </div>
                            
                            <div class="policy-section">
                                <h3 class="section-title"><i class="bi bi-envelope"></i>Contact Us</h3>
                                <div class="policy-content">
                                    <p>If you have any questions about this Privacy Policy, please contact us:</p>
                                    <ul>
                                        <li>By email: kaifsheikh126@gmail.com</li>
                                        <li>By visiting this page on our website: <a href="https://slaymart.site/">Slaymart Store</a> </li>
                                        <li>By phone number: [Your Phone Number]</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>