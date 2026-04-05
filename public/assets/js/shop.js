// Inicializuje spravanie shop stranky po nacitani DOM.
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(".shop-search-input");
  const headerCartTotal = document.getElementById("headerCartTotal");
  const headerCartCount = document.getElementById("headerCartCount");
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

  // Aktualizuje sumu a pocet kusov v hlavicke.
  function updateHeaderSummary(summary) {
    if (!summary) {
      return;
    }

    if (headerCartTotal) {
      const total = Number(summary.total || 0);
      headerCartTotal.textContent = total.toFixed(2) + " EUR";
    }

    if (headerCartCount) {
      const count = Math.max(0, Number(summary.count || 0));
      headerCartCount.textContent = String(count);
    }
  }

  if (searchInputs.length > 0) {
    searchInputs.forEach(function (searchInput) {
      searchInput.addEventListener("keyup", function () {
        const searchValue = this.value.toLowerCase();
        const products = document.querySelectorAll(".product-card");

        searchInputs.forEach(function (input) {
          if (input !== searchInput) {
            input.value = searchInput.value;
          }
        });

        products.forEach(function (product) {
          const productName = (product.dataset.name || "").toLowerCase();
          product.style.display = productName.includes(searchValue)
            ? "block"
            : "none";
        });
      });
    });
  }

  if (headerCartTotal || headerCartCount) {
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
          updateHeaderSummary(payload.summary);
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

function showCartToast(message, type) {
  const toast = document.createElement("div");
  toast.className =
    "cart-toast " + (type === "error" ? "is-error" : "is-success");
  toast.textContent = message || "Hotovo";

  document.body.appendChild(toast);

  // spusti animáciu
  requestAnimationFrame(function () {
    toast.classList.add("show");
  });

  // po 3s skry + odstráň
  setTimeout(function () {
    toast.classList.remove("show");
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 250);
  }, 3000);
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
        showCartToast(
          payload && payload.message
            ? payload.message
            : "Nepodarilo sa pridat produkt do kosika.",
          "error",
        );
        return;
      }

      const headerCartTotal = document.getElementById("headerCartTotal");
      if (headerCartTotal && payload.summary) {
        const total = Number(payload.summary.total || 0);
        headerCartTotal.textContent = total.toFixed(2) + " EUR";
      }

      const headerCartCount = document.getElementById("headerCartCount");
      if (headerCartCount && payload.summary) {
        const count = Math.max(0, Number(payload.summary.count || 0));
        headerCartCount.textContent = String(count);
      }

      showCartToast(
        payload.message || "Produkt bol pridany do kosika.",
        "success",
      );
    })
    .catch(function () {
      showCartToast("Chyba spojenia so serverom. Skus to znova.", "alert");
    });
}
