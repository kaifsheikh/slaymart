<?php
include "../../config/db.php";
include "../includes/session_check.php";

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Get image name from DB
    $res = mysqli_query($conn, "SELECT image FROM deals WHERE id=$id");
    $deal = mysqli_fetch_assoc($res);

    // Correct path to Images/deals (capital I)
    $imagePath = "../../images/deals/" . $deal['image'];

    // Delete image file if exists
    if ($deal && !empty($deal['image']) && file_exists($imagePath)) {
        unlink($imagePath);
    }

    // Delete record from DB
    mysqli_query($conn, "DELETE FROM deals WHERE id=$id");

    // Redirect with alert
    echo "<script>alert('✅ Deal Deleted!'); window.location='view-deals.php';</script>";
}
?>
