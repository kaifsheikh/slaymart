<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // User not logged in, show SweetAlert
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Required</title>
        <!-- SweetAlert2 CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.min.css">
    </head>
    <body>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.12/dist/sweetalert2.all.min.js"></script>

    <script>
        Swal.fire({
            title: 'Login Required!',
            text: 'You must login to continue.',
            icon: 'warning',
            confirmButtonText: 'Login Now',
            showCancelButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to login page, with redirect back to this page
                window.location.href = '../users/login.php?redirect=checkout.php&id=<?= $_GET['id'] ?>';
            }
        });
    </script>

    </body>
    </html>

    <?php
    exit();
}



// ✅ If user is logged in, redirect to actual checkout
$product_id = $_GET['id'] ?? '';
if (!$product_id) {
    die("Product ID is missing.");
}

header("Location: checkout.php?id=$product_id");
exit();
?>
