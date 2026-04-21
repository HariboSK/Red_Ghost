// Inicializuje spravanie shop stranky po nacitani DOM.
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(".shop-search-input");
  const headerSearchInput = document.getElementById("searchInput");
  const searchSuggestions = document.getElementById("searchSuggestions");
  const headerSearchForm = document.querySelector(".shop-header-search");
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

  // CART HOVER POPUP
  const cartIcon = document.getElementById("cartIcon");
  const cartPopup = document.getElementById("cartPopup");
  const cartItemsList = document.getElementById("cartItemsList");

  function loadCartPopup() {
    fetch(cartApiUrl + "?action=list", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (payload && payload.success && payload.items) {
          renderCartItems(payload.items);
        } else {
          cartItemsList.innerHTML =
            "<p style='padding: 10px; text-align: center;'>Košík je prázdny</p>";
        }
      })
      .catch(function () {
        cartItemsList.innerHTML =
          "<p style='padding: 10px; text-align: center; color: red;'>Chyba pri načítaní</p>";
      });
  }

  function renderCartItems(items) {
    if (!items || items.length === 0) {
      cartItemsList.innerHTML =
        "<p style='padding: 10px; text-align: center;'>Košík je prázdny</p>";
      return;
    }

    cartItemsList.innerHTML = "";
    items.forEach(function (item) {
      const itemEl = document.createElement("div");
      itemEl.className = "cart-item";

      const img = document.createElement("img");
      img.src = item.image || "/assets/images/omacka3.jpg";
      img.alt = item.name;

      const info = document.createElement("div");
      info.className = "cart-item-info";

      const nameP = document.createElement("p");
      nameP.className = "cart-item-name";
      nameP.textContent = item.name;

      const priceP = document.createElement("p");
      priceP.className = "cart-item-price";
      priceP.textContent =
        (item.price || 0).toFixed(2) + " EUR x " + (item.quantity || 1);

      info.appendChild(nameP);
      info.appendChild(priceP);

      const removeBtn = document.createElement("button");
      removeBtn.className = "cart-item-remove";
      removeBtn.type = "button";
      removeBtn.innerHTML = '<i class="fa fa-trash"></i>';
      removeBtn.onclick = function (e) {
        e.preventDefault();
        removeFromCart(item.id);
      };

      itemEl.appendChild(img);
      itemEl.appendChild(info);
      itemEl.appendChild(removeBtn);
      cartItemsList.appendChild(itemEl);
    });
  }

  function removeFromCart(id) {
    fetch(cartApiUrl + "?action=remove", {
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
        if (payload && payload.success) {
          loadCartPopup();
          updateHeaderSummary(payload.summary);
          showCartToast("Produkt bol odstránený", "success");
        } else {
          showCartToast("Chyba pri odstraňovaní", "error");
        }
      })
      .catch(function () {
        showCartToast("Chyba spojenia", "error");
      });
  }

  if (cartIcon && cartPopup) {
    cartIcon.addEventListener("mouseenter", function () {
      loadCartPopup();
      cartPopup.classList.add("is-visible");
      cartPopup.setAttribute("aria-hidden", "false");
      cartIcon.setAttribute("aria-expanded", "true");
    });

    cartIcon.addEventListener("mouseleave", function () {
      // Zopakuj po 200ms aby sa user dostal do popupu
      setTimeout(function () {
        if (!cartPopup.matches(":hover")) {
          cartPopup.classList.remove("is-visible");
          cartPopup.setAttribute("aria-hidden", "true");
          cartIcon.setAttribute("aria-expanded", "false");
        }
      }, 200);
    });

    cartPopup.addEventListener("mouseenter", function () {
      cartPopup.classList.add("is-visible");
      cartPopup.setAttribute("aria-hidden", "false");
    });

    cartPopup.addEventListener("mouseleave", function () {
      cartPopup.classList.remove("is-visible");
      cartPopup.setAttribute("aria-hidden", "true");
      cartIcon.setAttribute("aria-expanded", "false");
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
    const products = Array.from(document.querySelectorAll(".product-card"));

    function collectSuggestionSource() {
      const seen = new Set();

      return products
        .map(function (product) {
          const id = String(product.dataset.id || "");
          const name = String(product.dataset.name || "").trim();
          const price = Number(product.dataset.price || 0);
          const stock = Number(product.dataset.stock || 0);
          const image = product.querySelector("img");
          const imageUrl = image ? image.getAttribute("src") : "";
          const link = product.querySelector(".product-link");
          const url = link ? String(link.getAttribute("href") || "") : "";

          if (!id || !name || seen.has(id)) {
            return null;
          }

          seen.add(id);
          return {
            id: id,
            name: name,
            price: price,
            stock: stock,
            url: url || "/product?id=" + encodeURIComponent(id),
            image: imageUrl,
          };
        })
        .filter(function (item) {
          return item !== null;
        });
    }

    const suggestionSource = collectSuggestionSource();

    function hideSuggestions() {
      if (!searchSuggestions) {
        return;
      }

      searchSuggestions.classList.remove("is-visible");
      searchSuggestions.setAttribute("aria-hidden", "true");
      searchSuggestions.innerHTML = "";
    }

    function showSuggestions(query) {
      if (!searchSuggestions) {
        return;
      }

      const normalized = String(query || "")
        .trim()
        .toLowerCase();
      if (normalized.length < 2) {
        hideSuggestions();
        return;
      }

      const matches = suggestionSource
        .filter(function (item) {
          return item.name.toLowerCase().includes(normalized);
        })
        .slice(0, 6);

      if (matches.length === 0) {
        hideSuggestions();
        return;
      }

      const fragment = document.createDocumentFragment();

      matches.forEach(function (item) {
        const suggestionButton = document.createElement("button");
        suggestionButton.type = "button";
        suggestionButton.className = "search-suggestion-item";

        const nameSpan = document.createElement("span");
        nameSpan.className = "search-suggestion-name";
        nameSpan.textContent = item.name;

        const metaSpan = document.createElement("span");
        metaSpan.className = "search-suggestion-meta";
        metaSpan.textContent = item.price.toFixed(2) + " EUR";

        const img = document.createElement("img");
        img.className = "search-suggestion-image";
        img.src = item.image;
        img.alt = item.name;

        suggestionButton.appendChild(img);
        suggestionButton.appendChild(nameSpan);
        suggestionButton.appendChild(metaSpan);

        suggestionButton.addEventListener("click", function () {
          window.location.href = item.url;
        });

        fragment.appendChild(suggestionButton);
      });

      searchSuggestions.innerHTML = "";
      searchSuggestions.appendChild(fragment);
      searchSuggestions.classList.add("is-visible");
      searchSuggestions.setAttribute("aria-hidden", "false");
    }

    searchInputs.forEach(function (searchInput) {
      searchInput.addEventListener("input", function () {
        const searchValue = this.value.toLowerCase();

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

        if (searchInput === headerSearchInput) {
          showSuggestions(searchInput.value);
        }
      });
    });

    if (headerSearchInput && searchSuggestions) {
      headerSearchInput.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
          hideSuggestions();
          return;
        }

        if (event.key === "Enter") {
          const firstSuggestion = searchSuggestions.querySelector(
            ".search-suggestion-item",
          );
          if (firstSuggestion) {
            event.preventDefault();
            firstSuggestion.click();
          }
        }
      });

      document.addEventListener("click", function (event) {
        if (
          headerSearchForm &&
          !headerSearchForm.contains(event.target) &&
          searchSuggestions.contains(event.target) === false
        ) {
          hideSuggestions();
        }
      });
    }
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

function applyProductFilters() {
  const sortByElement = document.getElementById("sortBy");
  const priceFromElement = document.getElementById("priceFrom");
  const sortBy = sortByElement ? sortByElement.value : "price-asc";
  const minPrice = priceFromElement ? Number(priceFromElement.value || 0) : 0;
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
      const price = Number(product.dataset.price || 0);
      product.style.display = price >= minPrice ? "" : "none";
      container.appendChild(product);
    });
  });
}

// Zoradi produktove karty vo viditelnych kontajneroch podla zvolenej moznosti.
function sortProducts() {
  applyProductFilters();
}

function filterProducts() {
  applyProductFilters();
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
