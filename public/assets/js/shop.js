// Inicializuje spravanie shop stranky po nacitani DOM.
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const headerCartTotal = document.getElementById("headerCartTotal");
  const cartApiUrl = document.body.dataset.cartApi || "api/cart.php";
  const profileMenu = document.getElementById("profileMenu");
  const profileIcon = document.getElementById("profileIcon");
  const profilePopup = document.getElementById("profilePopup");

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

  // Aktualizuje sumu kosika zobrazenu v hlavicke.
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

  if (profileMenu && profileIcon && profilePopup) {
    profileIcon.setAttribute("aria-expanded", "false");

    profileIcon.addEventListener("click", function (event) {
      if (window.matchMedia("(max-width: 800px)").matches) {
        event.preventDefault();
        profileMenu.classList.toggle("open");
        const isOpen = profileMenu.classList.contains("open");
        profilePopup.setAttribute("aria-hidden", isOpen ? "false" : "true");
        profileIcon.setAttribute("aria-expanded", isOpen ? "true" : "false");
      }
    });

    document.addEventListener("click", function (event) {
      if (!profileMenu.contains(event.target)) {
        profileMenu.classList.remove("open");
        profilePopup.setAttribute("aria-hidden", "true");
        profileIcon.setAttribute("aria-expanded", "false");
      }
    });

    window.addEventListener("resize", function () {
      if (!window.matchMedia("(max-width: 800px)").matches) {
        profileMenu.classList.remove("open");
        profilePopup.setAttribute("aria-hidden", "true");
        profileIcon.setAttribute("aria-expanded", "false");
      }
    });
  }

  // Image zoom funckie pre produktove karty
  const zoomTrigger = document.getElementById("zoomTrigger");
  const zoomModal = document.getElementById("zoomModal");
  const closeModal = document.getElementById("closeModal");
  const zoomedImage = document.getElementById("zoomedImage");

  if (zoomTrigger && zoomModal && closeModal && zoomedImage) {
    zoomTrigger.addEventListener("click", function () {
      const sourceImage = zoomTrigger.querySelector("img");
      if (!sourceImage) {
        return;
      }

      zoomedImage.src = sourceImage.src;
      zoomedImage.alt = sourceImage.alt;
      zoomModal.classList.add("open");
      zoomModal.setAttribute("aria-hidden", "false");
    });

    closeModal.addEventListener("click", function () {
      zoomModal.classList.remove("open");
      zoomModal.setAttribute("aria-hidden", "true");
    });

    zoomModal.addEventListener("click", function (event) {
      if (event.target === zoomModal) {
        zoomModal.classList.remove("open");
        zoomModal.setAttribute("aria-hidden", "true");
      }
    });
  }
});

// Zoradi produktove karty vo viditelnych kontajneroch podla zvolenej moznosti.
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

// Posle ID produktu do cart API a aktualizuje sumu v hlavicke.
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
