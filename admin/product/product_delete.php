<?php
include "../../config/db.php"; 
include "../includes/session_check.php"; 

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); 
    $uploadDir = __DIR__ . "/../../images/uploads/"; // safer path

    // ---- Get & delete all product images ----
    $imgRes = mysqli_query($conn, "SELECT image FROM product_images WHERE product_id = $id");
    while ($imgRow = mysqli_fetch_assoc($imgRes)) {
        $filePath = $uploadDir . $imgRow['image'];
        if (!empty($imgRow['image']) && file_exists($filePath)) {
            unlink($filePath); // delete image file
        }
    }

    // ---- Delete child records first ----
    mysqli_query($conn, "DELETE FROM product_images WHERE product_id = $id");
    mysqli_query($conn, "DELETE FROM product_colors WHERE product_id = $id");
    mysqli_query($conn, "DELETE FROM product_sizes WHERE product_id = $id");

    // ---- Delete product ----
    mysqli_query($conn, "DELETE FROM products WHERE id = $id");

    // Redirect
    header("Location: manage_product.php?msg=deleted");
    exit;
}
?>
