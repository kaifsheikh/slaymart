// Fetch Categories Product
function loadProducts(category) {
  var xhttp = new XMLHttpRequest();
  xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
      document.getElementById("product-list").innerHTML = this.responseText;
    }
  };
  xhttp.open("GET", "ajax/fetch_products.php?category=" + encodeURIComponent(category), true);
  xhttp.send();
}
