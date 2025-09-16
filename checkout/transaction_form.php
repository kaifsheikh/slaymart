<?php
session_start();
if (!isset($_SESSION['pending_order'])) {
    header("Location: checkout.php");
    exit;
}
$order = $_SESSION['pending_order'];
$total_amount = (float)$_SESSION['pending_order']['price']; // JS se
$delivery_charges = isset($order['delivery_charges']) ? $order['delivery_charges'] : 0;
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slaymart - Payment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --amazon-blue: #131921;
            --amazon-orange: #FF9900;
            --amazon-light-orange: #FFD814;
            --amazon-dark-blue: #232F3E;
            --amazon-light-gray: #F7F7F7;
            --amazon-border: #D5D9D9;
            --amazon-text: #0F1111;
            --amazon-light-text: #565959;
            --amazon-star: #FFA41C;
            --border-radius: 4px;
            --transition: all 0.2s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #fff;
            color: var(--amazon-text);
            line-height: 1.5;
            font-size: 14px;
        }

        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .page-header {
            background: var(--amazon-blue);
            color: white;
            padding: 15px 0;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 400;
            margin: 0;
        }

        /* Main Content */
        .payment-card {
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid var(--amazon-border);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .payment-header {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
            padding: 14px 20px;
            font-weight: 500;
            font-size: 16px;
            border-bottom: 1px solid var(--amazon-border);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-header i {
            color: var(--amazon-orange);
        }

        .payment-body {
            padding: 30px;
        }

        /* Payment Methods */
        .payment-methods {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .payment-method {
            flex: 1;
            text-align: center;
            padding: 20px 15px;
            border-radius: var(--border-radius);
            background: white;
            border: 1px solid var(--amazon-border);
            cursor: pointer;
            transition: var(--transition);
        }

        .payment-method:hover {
            border-color: var(--amazon-orange);
        }

        .payment-method.active {
            border-color: var(--amazon-orange);
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
        }

        .payment-method i {
            font-size: 28px;
            color: var(--amazon-orange);
            margin-bottom: 10px;
        }

        .payment-method h6 {
            font-weight: 500;
            margin-bottom: 5px;
            font-size: 16px;
            color: var(--amazon-text);
        }

        .payment-method p {
            font-size: 14px;
            color: var(--amazon-light-text);
            margin: 0;
        }

        /* Payment Details */
        .payment-details {
            background: var(--amazon-light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid var(--amazon-border);
        }

        .payment-details h5 {
            font-weight: 500;
            margin-bottom: 15px;
            color: var(--amazon-text);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-details h5 i {
            color: var(--amazon-orange);
        }

        .payment-details p {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            color: var(--amazon-text);
        }

        .payment-details p:last-child {
            margin-bottom: 0;
        }

        .payment-details p i {
            color: var(--amazon-orange);
            width: 20px;
            text-align: center;
            margin-top: 3px;
        }

        /* Form Styles */
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--amazon-text);
            font-size: 14px;
        }

        .form-control {
            border-radius: var(--border-radius);
            border: 1px solid var(--amazon-border);
            padding: 10px 12px;
            font-size: 14px;
            transition: var(--transition);
            background-color: white;
        }

        .form-control:focus {
            border-color: var(--amazon-orange);
            box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.2);
            background-color: white;
        }

        /* Total Amount */
        .total-amount {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
            padding: 15px 20px;
            border-radius: var(--border-radius);
            text-align: center;
            margin: 25px 0;
            font-size: 16px;
            font-weight: 500;
            border: 1px solid var(--amazon-border);
        }

        .total-amount strong {
            font-size: 20px;
            color: var(--amazon-text);
        }

        /* Buttons */
        .btn {
            border-radius: var(--border-radius);
            font-weight: 500;
            padding: 10px 20px;
            transition: var(--transition);
            border: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--amazon-orange);
            color: var(--amazon-blue);
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--amazon-light-orange);
            color: var(--amazon-blue);
        }

        .btn-secondary {
            background: white;
            color: var(--amazon-text);
            border: 1px solid var(--amazon-border);
            width: 100%;
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: var(--amazon-light-gray);
            color: var(--amazon-text);
        }

        /* Error Message */
        .error-message {
            color: #B12704;
            font-size: 12px;
            margin-top: 5px;
            display: none;
        }

        .form-control.error {
            border-color: #B12704;
        }

        .form-control.error + .error-message {
            display: block;
        }

        .form-control.success {
            border-color: #007600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .payment-body {
                padding: 20px;
            }
            
            .payment-methods {
                flex-direction: column;
            }
            
            .payment-method {
                display: flex;
                align-items: center;
                text-align: left;
                gap: 15px;
                padding: 15px;
            }
            
            .payment-method i {
                font-size: 24px;
            }
            
            .payment-method h6 {
                margin-bottom: 0;
            }
        }
        
        @media (max-width: 576px) {
            .payment-container {
                padding: 0 10px;
            }
            
            .page-header {
                padding: 12px 15px;
            }
            
            .page-title {
                font-size: 20px;
            }
            
            .payment-header {
                padding: 12px 15px;
                font-size: 14px;
            }
            
            .payment-body {
                padding: 15px;
            }
            
            .payment-details p {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="payment-container">
            <h1 class="page-title">Payment</h1>
        </div>
    </div>
    
    <div class="payment-container">
        <div class="payment-card">
            <div class="payment-header">
                <i class="fas fa-credit-card"></i> Complete Payment
            </div>
            
            <div class="payment-body">
                <!-- Payment Methods -->
                <div class="payment-methods">
                    <div class="payment-method active">
                        <i class="fas fa-mobile-alt"></i>
                        <h6>EasyPaisa</h6>
                        <p>+(92) 312-8913161</p>
                    </div>
                    <div class="payment-method">
                        <i class="fas fa-university"></i>
                        <h6>Bank Transfer</h6>
                        <p>55270081006982018</p>
                    </div>
                </div>
                
                <!-- Payment Details -->
                <div class="payment-details">
                    <h5><i class="fas fa-info-circle"></i> Payment Instructions</h5>
                    <p><i class="fas fa-arrow-right"></i> Send the exact amount to the provided account</p>
                    <p><i class="fas fa-arrow-right"></i> Use your Order ID as the payment reference</p>
                    <p><i class="fas fa-arrow-right"></i> Transaction ID is required to verify your payment</p>
                    <p><i class="fas fa-arrow-right"></i> Payment confirmation may take up to 24 hours</p>
                </div>
                
                <form action="save_transaction.php" method="POST" id="paymentForm" novalidate>
                    <input type="hidden" name="price" value="<?php echo $total_amount; ?>">
                    <input type="hidden" name="delivery_charges" value="<?php echo $order['delivery_charges']; ?>">
                    
                    <!-- Hidden fields for color and size -->
                    <input type="hidden" name="color_id" value="<?php echo isset($order['color_id']) ? $order['color_id'] : 0; ?>">
                    <input type="hidden" name="size_id" value="<?php echo isset($order['size_id']) ? $order['size_id'] : 0; ?>">

                    <div class="mb-3">
                        <label for="transaction_id" class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Enter your transaction ID" required>
                        <div class="error-message">Please enter a valid transaction ID</div>
                    </div>
                    
                    <div class="total-amount">
                        Total Amount: <strong>Rs. <?php echo number_format($total_amount); ?></strong>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Submit Payment
                    </button>
                    
                    <a href="checkout.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Checkout
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('paymentForm');
            const transactionInput = document.getElementById('transaction_id');
            const paymentMethods = document.querySelectorAll('.payment-method');
            
            // Payment method selection
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Form validation
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                // Transaction ID validation
                const transactionId = transactionInput.value.trim();
                if (transactionId === '') {
                    showError(transactionInput, 'Transaction ID is required');
                    isValid = false;
                } else if (transactionId.length < 4) {
                    showError(transactionInput, 'Transaction ID must be at least 4 characters');
                    isValid = false;
                } else if (!/^[a-zA-Z0-9]+$/.test(transactionId)) {
                    showError(transactionInput, 'Transaction ID can only contain letters and numbers');
                    isValid = false;
                } else {
                    showSuccess(transactionInput);
                }
                
                if (!isValid) {
                    event.preventDefault();
                }
            });
            
            // Real-time validation
            transactionInput.addEventListener('input', function() {
                const transactionId = this.value.trim();
                
                if (transactionId === '') {
                    resetField(this);
                } else if (transactionId.length < 4) {
                    showError(this, 'Transaction ID must be at least 4 characters');
                } else if (!/^[a-zA-Z0-9]+$/.test(transactionId)) {
                    showError(this, 'Transaction ID can only contain letters and numbers');
                } else {
                    showSuccess(this);
                }
            });
            
            // Validation helper functions
            function showError(input, message) {
                input.classList.add('error');
                input.classList.remove('success');
                
                const errorDiv = input.nextElementSibling;
                errorDiv.textContent = message;
                errorDiv.style.display = 'block';
            }
            
            function showSuccess(input) {
                input.classList.add('success');
                input.classList.remove('error');
                
                const errorDiv = input.nextElementSibling;
                errorDiv.style.display = 'none';
            }
            
            function resetField(input) {
                input.classList.remove('error', 'success');
                
                const errorDiv = input.nextElementSibling;
                errorDiv.style.display = 'none';
            }
        });
    </script>
</body>
</html>