// Inicializuje spravanie shop stranky po nacitani DOM.
document.addEventListener("DOMContentLoaded", function () {
  const searchInputs = document.querySelectorAll(".shop-search-input");
  const headerSearchInput = document.getElementById("searchInput");
  const searchSuggestions = document.getElementById("searchSuggestions");
  const headerSearchForm = document.querySelector(".shop-header-search");
  const headerCartTotal = document.getElementById("headerCartTotal");
  const headerCartCount = document.getElementById("headerCartCount");
  const cartApiUrl = document.body.dataset.cartApi || "api/cart.php";
  const checkoutApiUrl = document.body.dataset.checkoutApi || "api/checkout.php";
  const profileMenu = document.getElementById("profileMenu");
  const profileIcon = document.getElementById("profileIcon");
  const profilePopup = document.getElementById("profilePopup");
  const shopcartState = window.__SHOPCART_STATE__ || {};

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
  const checkoutBtn = document.getElementById("checkoutBtn");
  const cartPageReturnTo = window.location.pathname + window.location.search;

  function formatMoney(value) {
    return Number(value || 0).toFixed(2) + " EUR";
  }

  function summarizeItems(items) {
    return items.reduce(
      function (summary, item) {
        const quantity = Math.max(0, Number(item.quantity || 0));
        const price = Number(item.price || 0);
        summary.count += quantity;
        summary.total += quantity * price;
        return summary;
      },
      { count: 0, total: 0 },
    );
  }

  function updateHeaderSummary(summary) {
    if (!summary) {
      return;
    }

    if (headerCartTotal) {
      headerCartTotal.textContent = formatMoney(summary.total || 0);
    }

    if (headerCartCount) {
      headerCartCount.textContent = String(Math.max(0, Number(summary.count || 0)));
    }
  }

  function renderCartPopupItems(items) {
    if (!cartItemsList) {
      return;
    }

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
        formatMoney(item.price || 0) + " x " + (item.quantity || 1);

      info.appendChild(nameP);
      info.appendChild(priceP);

      const actions = document.createElement("div");
      actions.className = "cart-item-actions";

      const createActionForm = function (actionName, iconClass, label) {
        const form = document.createElement("form");
        form.method = "POST";
        form.action = "/api/remove_cart.php";
        form.className = "cart-item-action-form";

        const idInput = document.createElement("input");
        idInput.type = "hidden";
        idInput.name = "id";
        idInput.value = String(item.id || "");

        const actionInput = document.createElement("input");
        actionInput.type = "hidden";
        actionInput.name = "action";
        actionInput.value = actionName;

        const returnToInput = document.createElement("input");
        returnToInput.type = "hidden";
        returnToInput.name = "return_to";
        returnToInput.value = cartPageReturnTo;

        const button = document.createElement("button");
        button.type = "submit";
        button.className = "cart-item-remove";
        button.title = label;
        button.setAttribute("aria-label", label);
        button.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';

        form.appendChild(idInput);
        form.appendChild(actionInput);
        form.appendChild(returnToInput);
        form.appendChild(button);

        return form;
      };

      const minusForm = createActionForm("decrement", "fa-solid fa-minus", "Odobrať 1 kus");
      const removeForm = createActionForm("remove", "fa-solid fa-trash-can", "Odstrániť z košíka");

      itemEl.appendChild(img);
      itemEl.appendChild(info);
      actions.appendChild(minusForm);
      actions.appendChild(removeForm);
      itemEl.appendChild(actions);
      cartItemsList.appendChild(itemEl);
    });
  }

  function refreshCartViews() {
    return fetch(cartApiUrl + "?action=list", {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        const items = payload && payload.success && Array.isArray(payload.items) ? payload.items : [];
        renderCartPopupItems(items);
        updateHeaderSummary(summarizeItems(items));
        return items;
      });
  }

  function updateCartQuantity(id, quantity) {
    // Quantity management is now handled server-side via form submission.
    // Keep a noop that informs developer/user when called from legacy code.
    return Promise.resolve({ success: false, message: 'Aktualizácia košíka cez JS nie je povolená.' });
  }

  function checkoutCart() {
    // Checkout should be performed via server-side POST form.
    showCartToast('Checkout presunutý na server; použite formulár.', 'error');
    return Promise.resolve({ success: false });
  }

  if (cartIcon && cartPopup) {
    cartIcon.addEventListener("mouseenter", function () {
      refreshCartViews().catch(function () {
        if (cartItemsList) {
          cartItemsList.innerHTML =
            "<p style='padding: 10px; text-align: center; color: red;'>Chyba pri načítaní</p>";
        }
      });
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

  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", function () {
      checkoutCart();
    });
  }

  refreshCartViews().catch(function () {});

  // Product search/suggestions and client-side product filtering removed.

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

// Client-side product filters removed — server-side implementation planned.

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

// Product add-to-cart via JS removed; will be handled server-side.
