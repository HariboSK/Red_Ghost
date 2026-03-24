document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');

  if (searchInput) {
    searchInput.addEventListener('keyup', function () {
      const searchValue = this.value.toLowerCase();
      const products = document.querySelectorAll('.product-card');

      products.forEach(function (product) {
        const productName = (product.dataset.name || '').toLowerCase();
        product.style.display = productName.includes(searchValue) ? 'block' : 'none';
      });
    });
  }
});

function sortProducts() {
  const sortBy = document.getElementById('sortBy').value;
  const container = document.getElementById('productsContainer');
  const products = Array.from(container.querySelectorAll('.product-card'));

  if (sortBy === 'popular') {
    products.sort(function (a, b) {
      return Number(b.dataset.price) - Number(a.dataset.price);
    });
  } else if (sortBy === '') {
    products.sort(function (a, b) {
      return Number(a.dataset.price) - Number(b.dataset.price);
    });
  }

  container.innerHTML = '';
  products.forEach(function (product) {
    container.appendChild(product);
  });
}

function addToCart(id, name, price) {
  const cart = JSON.parse(localStorage.getItem('cart')) || [];
  const existingItem = cart.find(function (item) {
    return item.id === id;
  });

  if (existingItem) {
    existingItem.quantity += 1;
  } else {
    cart.push({ id: id, name: name, price: price, quantity: 1 });
  }

  localStorage.setItem('cart', JSON.stringify(cart));
  alert(name + ' bolo pridané do košíka!');
}