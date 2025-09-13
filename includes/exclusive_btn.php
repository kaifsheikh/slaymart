<!-- Floating Exclusive Products Button -->
<a href="./exclusive_products/cart.php" class="exclusive-btn">
   <i class="fas fa-gem"></i> Exclusive
</a>

<!-- CSS -->
<style>
.exclusive-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background: linear-gradient(135deg, #ff0057, #ff7b00);
  color: white;
  font-size: 16px;
  font-weight: bold;
  padding: 12px 20px;
  border-radius: 50px;
  text-decoration: none;
  box-shadow: 0 4px 10px rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
  z-index: 9999;
}

.exclusive-btn i {
  font-size: 18px;
}

.exclusive-btn:hover {
  transform: scale(1.1);
  background: linear-gradient(135deg, #ff7b00, #ff0057);
}
</style>

<!-- FontAwesome (if not already included) -->
<script src="https://kit.fontawesome.com/yourkitid.js" crossorigin="anonymous"></script>
