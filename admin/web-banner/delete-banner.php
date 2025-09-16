<?php
include "../../config/db.php";
include "../includes/session_check.php";

$id = intval($_GET['id']);

// get image path before deleting
$result = mysqli_query($conn, "SELECT image FROM banners WHERE id=$id");
$row = mysqli_fetch_assoc($result);

if ($row) {
    $imagePath = "../../images/banners/" . $row['image'];

    // delete db record
    mysqli_query($conn, "DELETE FROM banners WHERE id=$id");

    // delete file only if exists and not empty
    if (!empty($row['image']) && file_exists($imagePath)) {
        unlink($imagePath);
    }

    echo "<script>alert('✅ Banner deleted successfully!'); window.location='view-banners.php';</script>";
} else {
    echo "<script>alert('❌ Banner not found!'); window.location='view-banners.php';</script>";
}
?>
