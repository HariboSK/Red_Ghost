document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const headerCartTotal = document.getElementById("headerCartTotal");
  const cartApiUrl = document.body.dataset.cartApi || "api/cart.php";

  if (
    typeof Swiper !== "undefined" &&
    document.querySelector(".shopBannerSwiper")
  ) {
    new Swiper(".shopBannerSwiper", {
      loop: true,
      speed: 700,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".shopBannerSwiper .swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".shopBannerSwiper .swiper-button-next",
        prevEl: ".shopBannerSwiper .swiper-button-prev",
      },
    });
  }

  function updateHeaderTotal(summary) {
    if (!headerCartTotal || !summary) {
      return;
    }

    const total = Number(summary.total || 0);
    headerCartTotal.textContent = total.toFixed(2) + " EUR";
  }

  if (searchInput) {
    searchInput.addEventListener("keyup", function () {
      const searchValue = this.value.toLowerCase();
      const products = document.querySelectorAll(".product-card");

      products.forEach(function (product) {
        const productName = (product.dataset.name || "").toLowerCase();
        product.style.display = productName.includes(searchValue)
          ? "block"
          : "none";
      });
    });
  }

  if (headerCartTotal) {
    fetch(cartApiUrl + "?action=summary", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
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
  const sortBy = document.getElementById("sortBy").value;
  const targetContainers = ["featuredProductsContainer", "productsContainer"];

  targetContainers.forEach(function (containerId) {
    const container = document.getElementById(containerId);
    if (!container) {
      return;
    }

    const products = Array.from(container.querySelectorAll(".product-card"));
    products.sort(function (a, b) {
      const priceA = Number(a.dataset.price || 0);
      const priceB = Number(b.dataset.price || 0);
      const ratingA = Number(a.dataset.rating || 0);
      const ratingB = Number(b.dataset.rating || 0);
      const stockA = Number(a.dataset.stock || 0);
      const stockB = Number(b.dataset.stock || 0);

      if (sortBy === "price-desc") {
        return priceB - priceA;
      }

      if (sortBy === "rating") {
        return ratingB - ratingA || priceA - priceB;
      }

      if (sortBy === "stock") {
        return stockB - stockA || priceA - priceB;
      }

      return priceA - priceB;
    });

    container.innerHTML = "";
    products.forEach(function (product) {
      container.appendChild(product);
    });
  });
}

function addToCart(id) {
  const cartApiUrl = document.body.dataset.cartApi || "api/cart.php";
  fetch(cartApiUrl + "?action=add", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify({ id: id }),
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (payload) {
      if (!payload || !payload.success) {
        alert("Nepodarilo sa pridat produkt do kosika.");
        return;
      }

      const headerCartTotal = document.getElementById("headerCartTotal");
      if (headerCartTotal && payload.summary) {
        const total = Number(payload.summary.total || 0);
        headerCartTotal.textContent = total.toFixed(2) + " EUR";
      }

      alert(payload.message || "Produkt bol pridany do kosika.");
    })
    .catch(function () {
      alert("Chyba spojenia so serverom. Skus to znova.");
    });
}
