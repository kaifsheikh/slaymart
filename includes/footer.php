<footer class="site-footer">
    <!-- Main Footer Content -->
    <div class="footer-main">
        <div class="container">
            <div class="row">
                <!-- About Company -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-widget">
                        <div class="footer-logo">
                            <img src="./images/logo/logo.png" alt="Slaymart Logo" class="logo-img">
                        </div>
                        <p class="footer-about">
                            Welcome to SlayMart.site – your trusted destination for a wide range of quality products. From the latest electronics and kitchen essentials to home décor, fitness gear, and lifestyle items, we bring everything you need under one roof. At SlayMart, we focus on delivering value, reliability, and convenience, ensuring every shopping experience is simple, secure, and satisfying.
                        </p>
                        <div class="social-links">
                            <a href="https://www.instagram.com/slaymartt_/" target="_blank" class="social-link"><i class="bi bi-instagram"></i></a>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Service -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <div class="footer-widget">
                        <h4 class="widget-title">Customer Service</h4>
                        <ul class="footer-links">
                            <li><a href="../web-info/shipping.php">Shipping Policy</a></li>
                            <li><a href="../web-info/policy.php">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-widget">
                        <h4 class="widget-title">Contact Us</h4>
                        <ul class="contact-info">
                            <li>
                                <i class="bi bi-geo-alt"></i>
                                <span>Pakistan Sindh, <br>Hyderabad, 17000</span>
                            </li>
                            <li>
                                <i class="bi bi-telephone"></i>
                                <span>+92 (310) 8422790</span>
                            </li>
                            <li>
                                <i class="bi bi-envelope"></i>
                                <span>kaifsheikh126@gmail.com</span>
                            </li>
                            <li>
                                <i class="bi bi-clock"></i>
                                <span>Mon - Fri: 9 AM - 8 PM<br>Sat - Sun: 10 AM - 6 PM</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.site-footer {
    background-color: #1a1a2e;
    color: #b8bec5;
    font-family: 'Poppins', sans-serif;
    margin-top: auto;
}

.footer-widget {
    margin-bottom: 30px;
}

.footer-logo {
    margin-bottom: 20px;
}

.logo-img {
    height: 100px;
    width: auto;
}

.footer-about {
    line-height: 1.8;
    margin-bottom: 25px;
    font-size: 0.95rem;
}

.widget-title {
    color: #ffffff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 12px;
}

.widget-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 2px;
    background-color: #4361ee;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links li a {
    color: #b8bec5;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.footer-links li a:hover {
    color: #4361ee;
    padding-left: 5px;
}

.contact-info {
    list-style: none;
    padding: 0;
    margin: 0;
}

.contact-info li {
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
}

.contact-info li i {
    color: #4361ee;
    font-size: 1.2rem;
    margin-right: 15px;
    margin-top: 3px;
    flex-shrink: 0;
}

.contact-info li span {
    line-height: 1.5;
}

.social-links {
    display: flex;
    gap: 12px;
}

.social-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: rgba(255, 255, 255, 0.1);
    color: #ffffff;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.social-link:hover {
    background-color: #4361ee;
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(67, 97, 238, 0.4);
}
.copyright {
    margin: 0;
    font-size: 0.9rem;
}

.copyright a {
    color: #4361ee;
    text-decoration: none;
    font-weight: 600;
}

.payment-methods {
    text-align: right;
}

.payment-img {
    max-height: 30px;
    width: auto;
}

/* Desktop/Laptop Specific Fixes */
@media (min-width: 992px) {
    .footer-main .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }
    
    .footer-main .col-lg-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding: 0 15px;
    }
    
    .footer-main .col-lg-2 {
        flex: 0 0 16.666667%;
        max-width: 16.666667%;
        padding: 0 15px;
    }
    
    .footer-widget {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .footer-logo {
        margin-bottom: 20px;
    }
    
    .footer-about {
        flex-grow: 1;
    }
    
    .social-links {
        margin-top: auto;
    }
    
}

/* Tablet Styles */
@media (min-width: 768px) and (max-width: 991px) {
    .footer-main .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -15px;
        margin-left: -15px;
    }
    
    .footer-main .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0 15px;
    }
    
}

/* Mobile Styles */
@media (max-width: 767px) {
    .footer-main {
        padding: 40px 0 30px;
    }
    
    .widget-title {
        font-size: 1.1rem;
    }
    
    .contact-info li {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .contact-info li i {
        margin-bottom: 8px;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .payment-methods {
        text-align: center;
        margin-top: 10px;
    }
}

/* Small Mobile Styles */
@media (max-width: 576px) {
    .footer-main {
        padding: 30px 0 20px;
    }
    
    .footer-about {
        font-size: 0.9rem;
    }
    
    .widget-title {
        font-size: 1rem;
        margin-bottom: 15px;
    }
    
    .footer-links li {
        margin-bottom: 8px;
    }
    
    .contact-info li {
        margin-bottom: 15px;
    }
    
    .payment-img {
        max-height: 25px;
    }
}
</style>

<!-- Animation Script -->
<script>
    AOS.init({
        once: false,
        offset: 120,
        duration: 500,
        easing: 'ease-in-out'
    });
</script>
<!--Icone Links-->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<!-- ionicon link -->
<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<!-- Categories Product Functionity -->
<script src="./assets/js/ajax_function.js"></script>