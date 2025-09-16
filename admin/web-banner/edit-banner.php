<?php
include "../../config/db.php";
include "../includes/session_check.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Old data
$result = mysqli_query($conn, "SELECT * FROM banners WHERE id=$id");
$banner = mysqli_fetch_assoc($result);

if (!$banner) {
  die("❌ Banner not found!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $title = !empty($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : NULL;
  $subtitle = !empty($_POST['subtitle']) ? mysqli_real_escape_string($conn, $_POST['subtitle']) : NULL;
  $price = !empty($_POST['price']) ? mysqli_real_escape_string($conn, $_POST['price']) : NULL;

  $imageDBPath = $banner['image']; // purana image agar new upload na ho

  // If new image uploaded
  if (!empty($_FILES['image']['name'])) {
    $targetDir = "../../images/banners/";
    $imageName = time() . "_" . basename($_FILES["image"]["name"]);
    $imagePath = $targetDir . $imageName;

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
      // delete old image
      $oldImagePath = $targetDir . $banner['image'];
      if (!empty($banner['image']) && file_exists($oldImagePath)) {
        unlink($oldImagePath);
      }
      $imageDBPath = $imageName;
    }
  }

  $query = "UPDATE banners SET 
            title=" . ($title !== NULL ? "'$title'" : "NULL") . ",
            subtitle=" . ($subtitle !== NULL ? "'$subtitle'" : "NULL") . ",
            price=" . ($price !== NULL ? "'$price'" : "NULL") . ",
            image='$imageDBPath'
          WHERE id=$id";

  mysqli_query($conn, $query) or die("DB Error: " . mysqli_error($conn));

  header("Location: view-banners.php?msg=updated");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Banner</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-lg-6 col-md-8">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title text-center mb-4">✏️ Edit Banner</h3>

            <form method="post" enctype="multipart/form-data">

              <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title"
                  value="<?= htmlspecialchars($banner['title'] ?? '') ?>"
                  class="form-control">
              </div>

              <div class="mb-3">
                <label class="form-label">Subtitle</label>
                <input type="text" name="subtitle"
                  value="<?= htmlspecialchars($banner['subtitle'] ?? '') ?>"
                  class="form-control">
              </div>

              <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price"
                  value="<?= htmlspecialchars($banner['price'] ?? '') ?>"
                  class="form-control">
              </div>

              <div class="mb-3">
                <label class="form-label">Upload New Image</label>
                <input type="file" name="image" class="form-control" accept="image/*">
              </div>

              <?php if ($banner['image']): ?>
                <div class="text-center mb-3">
                  <p class="small text-muted">Current Image:</p>
                  <img src="../../images/banners/<?= htmlspecialchars($banner['image']) ?>"
                    class="img-fluid rounded shadow-sm"
                    style="max-height: 180px;">
                </div>
              <?php endif; ?>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Update Banner</button>
                <a href="view-banners.php" class="btn btn-secondary btn-lg">Cancel</a>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>