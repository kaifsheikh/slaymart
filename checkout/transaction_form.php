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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }

        .payment-container {
            width: 100%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }

        .payment-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .payment-header p {
            opacity: 0.9;
            margin: 0;
        }

        .payment-body {
            padding: 30px;
        }

        .payment-methods {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .payment-method {
            flex: 1;
            text-align: center;
            padding: 15px 10px;
            border-radius: 12px;
            background: #f8f9fa;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #667eea;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }

        .payment-method.active {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .payment-method i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 8px;
        }

        .payment-method h6 {
            font-weight: 600;
            margin-bottom: 5px;
            font-size: 0.9rem;
        }

        .payment-method p {
            font-size: 0.8rem;
            color: #6c757d;
            margin: 0;
        }

        .payment-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .payment-details h5 {
            font-weight: 600;
            margin-bottom: 15px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-details p {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-details p:last-child {
            margin-bottom: 0;
        }

        .payment-details i {
            color: #667eea;
            width: 20px;
            text-align: center;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #e1e5e9;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .total-amount {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            border-radius: 12px;
            text-align: center;
            margin: 25px 0;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 12px 20px;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            width: 100%;
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: #e9ecef;
            color: #495057;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
            display: none;
        }

        .form-control.error {
            border-color: #e74c3c;
        }

        .form-control.error + .error-message {
            display: block;
        }

        .form-control.success {
            border-color: #27ae60;
        }

        /* Responsive Design */
        @media (max-width: 576px) {
            .payment-container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .payment-header {
                padding: 20px;
            }
            
            .payment-header h2 {
                font-size: 1.5rem;
            }
            
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
                padding: 12px 15px;
            }
            
            .payment-method i {
                font-size: 1.5rem;
            }
            
            .payment-method h6 {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <h2><i class="fas fa-credit-card"></i> Complete Payment</h2>
            <p>Please complete your payment to confirm your order</p>
        </div>
        
        <div class="payment-body">
            <!-- Payment Methods -->
            <div class="payment-methods">
                <div class="payment-method active">
                    <i class="fas fa-mobile-alt"></i>
                    <h6>EasyPaisa</h6>
                    <p>0312-8913161</p>
                </div>
                <div class="payment-method">
                    <i class="fas fa-university"></i>
                    <h6>Bank Transfer</h6>
                    <p>PK123456789</p>
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
                
                <div class="mb-3">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" name="transaction_id" id="transaction_id" class="form-control" placeholder="Enter your transaction ID" required>
                    <div class="error-message">Please enter a valid transaction ID</div>
                </div>
                
                <div class="total-amount">
                    Total Amount: Rs. <?php echo number_format($total_amount); ?>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-circle me-2"></i> Submit Payment
                </button>
                
                <a href="checkout.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Back to Checkout
                </a>
            </form>
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