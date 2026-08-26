(() => {
  const productList = document.getElementById('product-list');
  if (!productList) return;
  let activeRequest;

  const setActiveCategory = (category) => {
    document.querySelectorAll('[data-category]').forEach((item) => item.classList.toggle('is-active', item.dataset.category === category));
  };

  window.loadProducts = async (category = 'all') => {
    if (activeRequest) activeRequest.abort();
    activeRequest = new AbortController();
    productList.classList.add('is-loading');
    setActiveCategory(category);
    try {
      const response = await fetch(`ajax/fetch_products.php?category=${encodeURIComponent(category)}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: activeRequest.signal });
      if (!response.ok) throw new Error('Unable to load products');
      productList.innerHTML = await response.text();
      productList.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (error) {
      if (error.name !== 'AbortError') productList.innerHTML = '<div class="store-empty"><ion-icon name="alert-circle-outline"></ion-icon><h3>Could not load products</h3><p>Please refresh the page and try again.</p></div>';
    } finally {
      productList.classList.remove('is-loading');
    }
  };

  document.addEventListener('click', (event) => {
    const control = event.target.closest('[data-category]');
    if (!control) return;
    event.preventDefault();
    window.loadProducts(control.dataset.category);
  });
})();
