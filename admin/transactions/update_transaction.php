<?php
include "../../config/db.php";
include "../includes/session_check.php"; // Admin session check
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: users/admin_login.php");
    exit;
}
// Get transaction id
if (!isset($_GET['id'])) die("Transaction ID missing");
$transaction_id = intval($_GET['id']);
// Fetch existing transaction
$res = mysqli_query($conn, "SELECT * FROM transactions WHERE id=$transaction_id");
$transaction = mysqli_fetch_assoc($res);
// Update form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $amount = $_POST['amount'];
    $reference_no = $_POST['reference_no'];
    mysqli_query($conn, "UPDATE transactions SET status='$status', amount='$amount', reference_no='$reference_no' WHERE id=$transaction_id");
    header("Location: admin_transactions.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.08);
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-bottom: none;
            padding: 1.25rem;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .status-failed {
            background-color: #f8d7da;
            color: #842029;
        }
        
        @media (max-width: 576px) {
            .card {
                margin: 1rem 0;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Main Content -->
    <div class="container my-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>Update Transaction
                        </h4>
                        <span class="status-badge status-<?= $transaction['status'] ?>">
                            <?= ucfirst($transaction['status']) ?>
                        </span>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Transaction ID</label>
                                    <input type="text" class="form-control" value="<?= $transaction_id ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Date</label>
                                    <input type="text" class="form-control" value="<?= date('M j, Y', strtotime($transaction['created_at'])) ?>" readonly>
                                </div>
                                <div class="col-12">
                                    <label for="status" class="form-label fw-medium">Status</label>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="pending" <?= $transaction['status']=='pending'?'selected':'' ?>>
                                            <i class="bi bi-clock me-1"></i> Pending
                                        </option>
                                        <option value="success" <?= $transaction['status']=='success'?'selected':'' ?>>
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </option>
                                        <option value="failed" <?= $transaction['status']=='failed'?'selected':'' ?>>
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="amount" class="form-label fw-medium">Amount ($)</label>
                                    <input type="number" name="amount" id="amount" 
                                           value="<?= $transaction['amount'] ?>" 
                                           class="form-control" required step="0.01">
                                </div>
                                <div class="col-md-6">
                                    <label for="reference_no" class="form-label fw-medium">Reference Number</label>
                                    <input type="text" name="reference_no" id="reference_no" 
                                           value="<?= htmlspecialchars($transaction['reference_no']) ?>" 
                                           class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <div class="d-flex flex-column flex-sm-row gap-2 mt-4">
                                        <button type="submit" class="btn btn-primary flex-fill">
                                            <i class="bi bi-check-lg me-1"></i> Update Transaction
                                        </button>
                                        <a href="admin_transactions.php" class="btn btn-outline-secondary flex-fill">
                                            <i class="bi bi-x-lg me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Additional Info Card -->
                <div class="card mt-2">
                    <div class="card-header bg-light">
                        <h5 class="mb-0 text-dark"><i class="bi bi-info-circle me-2 text-dark"></i>Transaction Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>User ID:</strong> <?= $transaction['user_id'] ?></p>
                                <p class="mb-1"><strong>Payment Method:</strong> <?= ucfirst($transaction['payment_method']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <p class="mb-1"><strong>Description:</strong> <?= htmlspecialchars($transaction['description']) ?></p>
                                <p class="mb-1"><strong>Created:</strong> <?= date('F j, Y, g:i a', strtotime($transaction['created_at'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>