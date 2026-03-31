document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('searchInput');
  const headerCartTotal = document.getElementById('headerCartTotal');
  const cartApiUrl = document.body.dataset.cartApi || 'api/cart.php';

  function updateHeaderTotal(summary) {
    if (!headerCartTotal || !summary) {
      return;
    }

    const total = Number(summary.total || 0);
    headerCartTotal.textContent = total.toFixed(2) + ' EUR';
  }

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

  if (headerCartTotal) {
    fetch(cartApiUrl + '?action=summary', {
      method: 'GET',
      headers: {
        'Accept': 'application/json'
      }
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (payload && payload.success && payload.summary) {
          updateHeaderTotal(payload.summary);
        }
      })
      .catch(function () {
        // Keep default value when API is unavailable.
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

function addToCart(id) {
  const cartApiUrl = document.body.dataset.cartApi || 'api/cart.php';
  fetch(cartApiUrl + '?action=add', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ id: id })
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (payload) {
      if (!payload || !payload.success) {
        alert('Nepodarilo sa pridat produkt do kosika.');
        return;
      }

      const headerCartTotal = document.getElementById('headerCartTotal');
      if (headerCartTotal && payload.summary) {
        const total = Number(payload.summary.total || 0);
        headerCartTotal.textContent = total.toFixed(2) + ' EUR';
      }

      alert(payload.message || 'Produkt bol pridany do kosika.');
    })
    .catch(function () {
      alert('Chyba spojenia so serverom. Skus to znova.');
    });
}