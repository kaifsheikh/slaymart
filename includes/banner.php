<?php
$query = "SELECT * FROM banners WHERE status = 'active' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$first = true;
?>

<div class="banner" data-aos="fade-up" data-aos-delay="500">
  <div class="container">
    <div class="slider-container">

      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="slider-item <?php echo $first ? 'active' : ''; ?>">
          <img src="images/banners/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>" class="banner-img" alt="<?php echo htmlspecialchars($row['title'] ?: 'SlayMart promotion', ENT_QUOTES, 'UTF-8'); ?>">
          
          <div class="banner-content">
  <?php if (!empty($row['subtitle'])) { ?>
    <p class="banner-subtitle"><?php echo htmlspecialchars($row['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
  <?php } ?>
  
  <?php if (!empty($row['title'])) { ?>
    <h2 class="banner-title"><?php echo htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
  <?php } ?>
  
  <?php if (!empty($row['price'])) { ?>
    <p class="banner-text">starting at <b>PKR <?php echo number_format((float) $row['price']); ?></b></p>
  <?php } ?>
  
  <a href="#product-list" class="banner-btn">Shop now</a>
</div>
        </div>
      <?php $first = false; } ?>

    </div>
  </div>
</div>


<script>
  let sliderItems = document.querySelectorAll(".slider-item");
  let currentIndex = 0;

  function showSlide(index) {
    sliderItems.forEach(item => item.classList.remove("active"));
    sliderItems[index].classList.add("active");
  }

  if (sliderItems.length) {
    showSlide(currentIndex);
    if (sliderItems.length > 1) setInterval(() => {
      currentIndex = (currentIndex + 1) % sliderItems.length;
      showSlide(currentIndex);
    }, 7000);
  }
</script>

