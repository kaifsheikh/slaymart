<style>
  .slider-container {
  position: relative;
  width: 100%;
  height: 400px; /* container ki height fix karo */
  overflow: hidden;
}

.slider-item {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 1s ease-in-out; /* smooth fade */
}

.slider-item img {
  width: 100%;
  height: 100%;
  object-fit: cover; /* image ko fit karne ke liye */
  display: block;
}

.slider-item.active {
  opacity: 1;
  z-index: 1;
}

</style>

<?php
$query = "SELECT * FROM banners";
$result = mysqli_query($conn, $query);
$first = true;
?>

<div class="banner" data-aos="fade-up" data-aos-delay="500">
  <div class="container">
    <div class="slider-container">

      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="slider-item <?php echo $first ? 'active' : ''; ?>">
          <img src="images/banners/<?php echo $row['image']; ?>" class="banner-img" alt="banner">
          <div class="banner-content">
            <p class="banner-subtitle"><?php echo $row['subtitle']; ?></p>
            <h2 class="banner-title"><?php echo $row['title']; ?></h2>
            <p class="banner-text">starting at <b><?php echo $row['price']; ?></b></p>
            <a href="" class="banner-btn">Shop now</a>
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

  // Show first slide
  showSlide(currentIndex);

  // Auto change every 10 sec
  setInterval(() => {
    currentIndex = (currentIndex + 1) % sliderItems.length;
    showSlide(currentIndex);
  }, 10000);
</script>

