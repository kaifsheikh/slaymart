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
  background: #FF9900;
  color: #131921;
  font-family: 'Roboto', sans-serif;
  font-size: 14px;
  font-weight: 500;
  padding: 10px 16px;
  border-radius: 20px;
  text-decoration: none;
  box-shadow: 0 2px 5px rgba(0,0,0,0.15);
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  z-index: 9999;
  border: 1px solid #FFD814;
}

.exclusive-btn i {
  font-size: 16px;
}

.exclusive-btn:hover {
  background: #FFD814;
  color: #131921;
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.exclusive-btn:focus {
  outline: none;
  box-shadow: 0 0 0 2px rgba(255, 153, 0, 0.5);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .exclusive-btn {
    bottom: 15px;
    right: 15px;
    padding: 8px 14px;
    font-size: 13px;
  }
  
  .exclusive-btn i {
    font-size: 14px;
  }
}

@media (max-width: 576px) {
  .exclusive-btn {
    bottom: 10px;
    right: 10px;
    padding: 8px 12px;
    font-size: 12px;
  }
  
  .exclusive-btn i {
    font-size: 12px;
  }
}
</style>

<!-- FontAwesome (if not already included) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">