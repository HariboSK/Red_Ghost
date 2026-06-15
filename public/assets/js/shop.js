// Inicializuje spravanie shop stranky po nacitani DOM.
document.addEventListener("DOMContentLoaded", function () {
  // Získanie CSRF tokenu z meta tagu v hlavičke
  var csrfMeta = document.querySelector('meta[name="csrf-token"]');
  var csrfToken = csrfMeta ? csrfMeta.content : "";

  var searchInputs = document.querySelectorAll(".shop-search-input");
  var headerSearchInput = document.getElementById("searchInput");
  var searchSuggestions = document.getElementById("searchSuggestions");
  var headerSearchForm = document.querySelector(".shop-header-search");
  var headerSearchButton = headerSearchForm ? headerSearchForm.querySelector(".shop-header-search-btn") : null;
  var shopSearchSummary = document.getElementById("shopSearchSummary");
  var shopNoResults = document.getElementById("shopNoResults");
  var clearShopFiltersBtn = document.getElementById("clearShopFilters");
  var clearShopFiltersEmptyBtn = document.getElementById("clearShopFiltersEmpty");
  var priceFromSelect = document.getElementById("priceFrom");
  var sortBySelect = document.getElementById("sortBy");
  var headerCartTotal = document.getElementById("headerCartTotal");
  var headerCartCount = document.getElementById("headerCartCount");
  var cartApiUrl = document.body.dataset.cartApi || "api/Cart.php";
  var checkoutApiUrl = document.body.dataset.checkoutApi || "api/checkout.php";
  var profileMenu = document.getElementById("profileMenu");
  var profileIcon = document.getElementById("profileIcon");
  var profilePopup = document.getElementById("profilePopup");
  var shopcartState = window.__SHOPCART_STATE__ || {};
  var featuredProductsContainer = document.getElementById("featuredProductsContainer");
  var productsContainer = document.getElementById("productsContainer");
  var catalogState = {
    query: "",
    minPrice: priceFromSelect ? Number(priceFromSelect.value || 0) : 0,
    sortBy: sortBySelect ? String(sortBySelect.value || "price-asc") : "price-asc",
  };

  function normalizeText(value) {
    return String(value || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "")
      .replace(/[^a-z0-9\s]/g, " ")
      .replace(/\s+/g, " ")
      .trim();
  }

  function escapeHtml(value) {
    return String(value || "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  function getCardData(card) {
    var name = card.getAttribute("data-name") || "";
    var category = card.getAttribute("data-category") || "";
    var searchTerms = card.getAttribute("data-search") || (name + " " + category);
    var price = Number(card.getAttribute("data-price") || 0);
    var rating = Number(card.getAttribute("data-rating") || 0);
    var stock = Number(card.getAttribute("data-stock") || 0);
    var link = card.querySelector(".product-link");
    var image = card.querySelector("img");

    return {
      card: card,
      id: card.getAttribute("data-id") || "",
      name: name,
      category: category,
      searchTerms: normalizeText(searchTerms),
      price: price,
      rating: rating,
      stock: stock,
      href: link ? link.getAttribute("href") || "#" : "#",
      image: image ? image.getAttribute("src") || "/assets/images/omacka3.webp" : "/assets/images/omacka3.webp",
    };
  }

  function getAllCards() {
    return Array.prototype.slice.call(document.querySelectorAll(".product-card"));
  }

  function getCardsByContainer(container) {
    if (!container) {
      return [];
    }

    return Array.prototype.slice.call(container.querySelectorAll(".product-card"));
  }

  function compareCards(a, b, sortBy) {
    var priceA = Number(a.getAttribute("data-price") || 0);
    var priceB = Number(b.getAttribute("data-price") || 0);
    var ratingA = Number(a.getAttribute("data-rating") || 0);
    var ratingB = Number(b.getAttribute("data-rating") || 0);
    var stockA = Number(a.getAttribute("data-stock") || 0);
    var stockB = Number(b.getAttribute("data-stock") || 0);
    var nameAEl = a.querySelector("h4");
    var nameBEl = b.querySelector("h4");
    var nameA = normalizeText(a.getAttribute("data-name") || (nameAEl ? nameAEl.textContent : "") || "");
    var nameB = normalizeText(b.getAttribute("data-name") || (nameBEl ? nameBEl.textContent : "") || "");

    if (sortBy === "price-desc") {
      return priceB - priceA || nameA.localeCompare(nameB);
    }

    if (sortBy === "rating") {
      return ratingB - ratingA || priceA - priceB || nameA.localeCompare(nameB);
    }

    if (sortBy === "stock") {
      return stockB - stockA || priceA - priceB || nameA.localeCompare(nameB);
    }

    return priceA - priceB || nameA.localeCompare(nameB);
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

  function formatMoney(value) {
    return Number(value || 0).toFixed(2) + " EUR";
  }

  function renderSuggestions(matches, query) {
    if (!searchSuggestions) {
      return;
    }

    var list = matches.slice(0, 6);

    if (!query || normalizeText(query).length < 2) {
      searchSuggestions.classList.remove("is-visible");
      searchSuggestions.setAttribute("aria-hidden", "true");
      searchSuggestions.innerHTML = "";
      return;
    }

    if (list.length === 0) {
      searchSuggestions.innerHTML = '<div class="search-suggestion-empty">Žiadne návrhy nenájdené.</div>';
      searchSuggestions.classList.add("is-visible");
      searchSuggestions.setAttribute("aria-hidden", "false");
      return;
    }

    searchSuggestions.innerHTML = list.map(function (item) {
      var priceLabel = item.price > 0 ? formatMoney(item.price) : "";
      var stockLabel = item.stock > 0 ? "Skladom " + item.stock + " ks" : "Vypredané";
      return [
        '<a class="search-suggestion-item" href="' + escapeHtml(item.href) + '" data-target-id="' + escapeHtml(item.id) + '" aria-label="Zobraziť produkt ' + escapeHtml(item.name) + '">',
        '<img class="search-suggestion-image" src="' + escapeHtml(item.image) + '" alt="">',
        '<span class="search-suggestion-text">',
        '<span class="search-suggestion-name">' + escapeHtml(item.name) + '</span>',
        '<span class="search-suggestion-meta">' + escapeHtml(item.category || "Produkt") + '</span>',
        '</span>',
        '<span class="search-suggestion-price">' + escapeHtml(priceLabel || stockLabel) + '</span>',
        '</a>',
      ].join("");
    }).join("");

    searchSuggestions.classList.add("is-visible");
    searchSuggestions.setAttribute("aria-hidden", "false");
  }

  function scoreSuggestion(item, normalizedQuery) {
    var name = normalizeText(item.name);
    var category = normalizeText(item.category);
    var terms = normalizeText(item.searchTerms);
    var score = 0;

    if (!normalizedQuery || normalizedQuery.length < 2) {
      return 0;
    }

    if (name === normalizedQuery) {
      score += 100;
    } else if (name.indexOf(normalizedQuery) === 0) {
      score += 80;
    } else if (name.indexOf(normalizedQuery) !== -1) {
      score += 60;
    }

    if (category.indexOf(normalizedQuery) === 0) {
      score += 30;
    } else if (category.indexOf(normalizedQuery) !== -1) {
      score += 20;
    }

    if (terms.indexOf(normalizedQuery) !== -1) {
      score += 10;
    }

    score += Math.max(0, 10 - Math.min(10, item.rating || 0));
    if ((item.stock || 0) > 0) {
      score += 3;
    }

    return score;
  }

  function getMatches(query) {
    var normalizedQuery = normalizeText(query);
    var cards = getAllCards().map(getCardData);

    if (!normalizedQuery || normalizedQuery.length < 2) {
      return [];
    }

    return cards
      .map(function (item) {
        return {
          item: item,
          score: scoreSuggestion(item, normalizedQuery),
        };
      })
      .filter(function (entry) {
        return entry.score > 0;
      })
      .sort(function (a, b) {
        return b.score - a.score || a.item.name.localeCompare(b.item.name);
      })
      .map(function (entry) {
        return entry.item;
      });
  }

  function updateSearchSummary(visibleCount, totalCount) {
    if (!shopSearchSummary) {
      return;
    }

    var query = String(catalogState.query || "").trim();
    var minPrice = Number(catalogState.minPrice || 0);
    var sortBy = String(catalogState.sortBy || "price-asc");

    if (visibleCount === 0) {
      shopSearchSummary.textContent = "Nenašli sa žiadne produkty.";
      return;
    }

    var parts = [];
    if (query) {
      parts.push('Hľadajú sa výsledky pre "' + query + '"');
    } else {
      parts.push("Zobrazené sú všetky produkty");
    }

    if (minPrice > 0) {
      parts.push("cena od " + minPrice.toFixed(0) + " EUR");
    }

    if (sortBy === "price-desc") {
      parts.push("zoradené od najdrahších");
    } else if (sortBy === "rating") {
      parts.push("zoradené podľa hodnotenia");
    } else if (sortBy === "stock") {
      parts.push("zoradené podľa skladu");
    } else {
      parts.push("zoradené od najlacnejších");
    }

    parts.push(visibleCount + " / " + totalCount);
    shopSearchSummary.textContent = parts.join(" · ");
  }

  function scrollToFirstVisibleResult() {
    var firstVisibleCard = document.querySelector(".product-card:not(.is-hidden)");
    if (firstVisibleCard && typeof firstVisibleCard.scrollIntoView === "function") {
      firstVisibleCard.scrollIntoView({ behavior: "smooth", block: "start" });
    }
  }

  function applyCatalogFilters(options) {
    var focusResults = Boolean(options && options.focusResults);
    var query = normalizeText(catalogState.query);
    var minPrice = Number(catalogState.minPrice || 0);
    var sortBy = String(catalogState.sortBy || "price-asc");
    var visibleCount = 0;
    var featuredCards = getCardsByContainer(featuredProductsContainer);
    var otherCards = getCardsByContainer(productsContainer);

    function processContainer(container, cards) {
      if (!container || !cards.length) {
        return;
      }

      cards = cards.slice().sort(function (a, b) {
        return compareCards(a, b, sortBy);
      });

      cards.forEach(function (card) {
        var searchTerms = normalizeText(card.getAttribute("data-search") || card.getAttribute("data-name") || "");
        var price = Number(card.getAttribute("data-price") || 0);
        var matchesQuery = !query || searchTerms.indexOf(query) !== -1;
        var matchesPrice = price >= minPrice;
        var isVisible = matchesQuery && matchesPrice;

        card.classList.toggle("is-hidden", !isVisible);
        card.setAttribute("aria-hidden", isVisible ? "false" : "true");

        if (isVisible) {
          visibleCount += 1;
        }

        container.appendChild(card);
      });
    }

    processContainer(featuredProductsContainer, featuredCards);
    processContainer(productsContainer, otherCards);

    if (shopNoResults) {
      var hasResults = visibleCount > 0;
      shopNoResults.hidden = hasResults;
      shopNoResults.classList.toggle("is-visible", !hasResults);
    }

    updateSearchSummary(visibleCount, featuredCards.length + otherCards.length);

    if (focusResults && query) {
      scrollToFirstVisibleResult();
    }

    return visibleCount;
  }

  function syncSearchInputs(value) {
    Array.prototype.forEach.call(searchInputs, function (input) {
      if (input && input.value !== value) {
        input.value = value;
      }
    });
  }

  function updateSuggestionsFromInput() {
    var value = headerSearchInput ? headerSearchInput.value : "";
    renderSuggestions(getMatches(value), value);
  }

  function setSearchQuery(value, options) {
    catalogState.query = String(value || "");
    syncSearchInputs(catalogState.query);
    updateSuggestionsFromInput();
    applyCatalogFilters(options || {});
  }

  function clearFilters() {
    catalogState.query = "";
    catalogState.minPrice = 0;
    catalogState.sortBy = "price-asc";

    syncSearchInputs("");

    if (headerSearchInput) {
      headerSearchInput.value = "";
    }

    if (priceFromSelect) {
      priceFromSelect.value = "0";
    }

    if (sortBySelect) {
      sortBySelect.value = "price-asc";
    }

    if (searchSuggestions) {
      searchSuggestions.innerHTML = "";
      searchSuggestions.classList.remove("is-visible");
      searchSuggestions.setAttribute("aria-hidden", "true");
    }

    applyCatalogFilters({ focusResults: false });
  }

  if (
    typeof Swiper !== "undefined" &&
    document.querySelector(".shopBannerSwiper")
  ) {
    new Swiper(".shopBannerSwiper", {
      loop: false,
      rewind: true,
      speed: 750,
      slidesPerView: 1,
      watchOverflow: true,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".shopBannerSwiper .swiper-pagination",
        clickable: true,
        renderBullet: function (index, className) {
          return '<button type="button" class="' + className + '" aria-label="Prejsť na snímku ' + (index + 1) + '"></button>';
        },
      },
      navigation: {
        nextEl: ".shopBannerSwiper .swiper-button-next",
        prevEl: ".shopBannerSwiper .swiper-button-prev",
      },
    });
  }

  // CART HOVER POPUP
  var cartIcon = document.getElementById("cartIcon");
  var cartPopup = document.getElementById("cartPopup");
  var cartItemsList = document.getElementById("cartItemsList");
  var checkoutBtn = document.getElementById("checkoutBtn");
  var cartPageReturnTo = window.location.pathname + window.location.search;

  function summarizeItems(items) {
    return items.reduce(function (summary, item) {
      var quantity = Math.max(0, Number(item.quantity || 0));
      var price = Number(item.price || 0);
      summary.count += quantity;
      summary.total += quantity * price;
      return summary;
    }, { count: 0, total: 0 });
  }

  function renderCartPopupItems(items) {
    if (!cartItemsList) {
      return;
    }

    if (!items || items.length === 0) {
      cartItemsList.innerHTML = "<p style='padding: 10px; text-align: center;'>Košík je prázdny</p>";
      return;
    }

    cartItemsList.innerHTML = "";
    items.forEach(function (item) {
      var itemEl = document.createElement("div");
      itemEl.className = "cart-item";

      var img = document.createElement("img");
      img.src = item.image || "/assets/images/omacka3.webp";
      img.alt = item.name;

      var info = document.createElement("div");
      info.className = "cart-item-info";

      var nameP = document.createElement("p");
      nameP.className = "cart-item-name";
      nameP.textContent = item.name;

      var priceP = document.createElement("p");
      priceP.className = "cart-item-price";
      priceP.textContent = formatMoney(item.price || 0) + " x " + (item.quantity || 1);

      info.appendChild(nameP);
      info.appendChild(priceP);

      var actions = document.createElement("div");
      actions.className = "cart-item-actions";

      var createActionForm = function (actionName, iconClass, label) {
        var form = document.createElement("form");
        form.method = "POST";
        form.action = "/api/RemoveCart.php";
        form.className = "cart-item-action-form";

        var idInput = document.createElement("input");
        idInput.type = "hidden";
        idInput.name = "id";
        idInput.value = String(item.id || "");

        var actionInput = document.createElement("input");
        actionInput.type = "hidden";
        actionInput.name = "action";
        actionInput.value = actionName;

        var returnToInput = document.createElement("input");
        returnToInput.type = "hidden";
        returnToInput.name = "return_to";
        returnToInput.value = cartPageReturnTo;

        // --- PRIDANIE CSRF TOKENU DO FORMULÁRA ---
        var csrfInput = document.createElement("input");
        csrfInput.type = "hidden";
        csrfInput.name = "csrf_token";
        csrfInput.value = csrfToken;

        var button = document.createElement("button");
        button.type = "submit";
        button.className = "cart-item-remove";
        button.title = label;
        button.setAttribute("aria-label", label);
        button.innerHTML = '<i class="' + iconClass + '" aria-hidden="true"></i>';

        form.appendChild(idInput);
        form.appendChild(actionInput);
        form.appendChild(returnToInput);
        form.appendChild(csrfInput); // Vloženie tokenu
        form.appendChild(button);

        return form;
      };

      var minusForm = createActionForm("decrement", "fa-solid fa-minus", "Odobrať 1 kus");
      var removeForm = createActionForm("remove", "fa-solid fa-trash-can", "Odstrániť z košíka");

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
        var items = payload && payload.success && Array.isArray(payload.items) ? payload.items : [];
        renderCartPopupItems(items);
        updateHeaderSummary(summarizeItems(items));
        return items;
      });
  }

  function checkoutCart() {
    if (checkoutBtn && checkoutBtn.dataset.paymentUrl) {
      window.location.href = checkoutBtn.dataset.paymentUrl;
      return Promise.resolve({ success: true });
    }

    return Promise.resolve({ success: false });
  }

  var cartMenu = document.getElementById("cartMenu");
  var cartPopupHideTimer = null;
  var faqWidget = document.getElementById("faqWidget");
  var faqWidgetToggle = document.getElementById("faqWidgetToggle");
  var faqWidgetPanel = document.getElementById("faqWidgetPanel");
  var faqWidgetBackdrop = document.getElementById("faqWidgetBackdrop");
  var faqWidgetClose = document.getElementById("faqWidgetClose");

  function setCartPopupVisible(visible) {
    if (!cartPopup) {
      return;
    }

    cartPopup.classList.toggle("is-visible", visible);
    cartPopup.setAttribute("aria-hidden", visible ? "false" : "true");
    if (cartIcon) {
      cartIcon.setAttribute("aria-expanded", visible ? "true" : "false");
    }
  }

  function openCartPopup() {
    if (!cartPopup) {
      return;
    }

    clearTimeout(cartPopupHideTimer);
    setCartPopupVisible(true);
    refreshCartViews().catch(function () {});
  }

  function closeCartPopup() {
    clearTimeout(cartPopupHideTimer);
    setCartPopupVisible(false);
  }

  function scheduleCloseCartPopup() {
    clearTimeout(cartPopupHideTimer);
    cartPopupHideTimer = window.setTimeout(function () {
      if (cartIcon && cartIcon.matches(":hover")) {
        return;
      }

      if (cartPopup && cartPopup.matches(":hover")) {
        return;
      }

      closeCartPopup();
    }, 160);
  }

  if (cartIcon && cartPopup) {
    cartIcon.addEventListener("mouseenter", openCartPopup);
    cartPopup.addEventListener("mouseenter", openCartPopup);
    cartIcon.addEventListener("mouseleave", scheduleCloseCartPopup);
    cartPopup.addEventListener("mouseleave", scheduleCloseCartPopup);
    cartIcon.addEventListener("click", function (event) {
      if (window.matchMedia("(hover: none), (pointer: coarse)").matches) {
        event.preventDefault();
        if (cartPopup.classList.contains("is-visible")) {
          closeCartPopup();
        } else {
          openCartPopup();
        }
      }
    });

    document.addEventListener("click", function (event) {
      if (cartMenu && !cartMenu.contains(event.target)) {
        closeCartPopup();
      }
    });
  }

  function setFaqWidgetOpen(isOpen) {
    if (!faqWidget || !faqWidgetPanel || !faqWidgetBackdrop || !faqWidgetToggle) {
      return;
    }

    faqWidget.classList.toggle("is-open", isOpen);
    faqWidgetPanel.classList.toggle("is-open", isOpen);
    faqWidgetPanel.setAttribute("aria-hidden", isOpen ? "false" : "true");
    faqWidgetToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
    faqWidgetBackdrop.hidden = !isOpen;
  }

  function openFaqWidget() {
    setFaqWidgetOpen(true);
  }

  function closeFaqWidget() {
    setFaqWidgetOpen(false);
  }

  if (faqWidgetToggle && faqWidgetPanel && faqWidgetBackdrop && faqWidgetClose) {
    faqWidgetToggle.addEventListener("click", function () {
      openFaqWidget();
    });

    faqWidgetClose.addEventListener("click", closeFaqWidget);

    faqWidgetBackdrop.addEventListener("click", closeFaqWidget);

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && faqWidgetPanel.classList.contains("is-open")) {
        closeFaqWidget();
      }
    });
  }

  if (searchInputs.length) {
    Array.prototype.forEach.call(searchInputs, function (input) {
      input.addEventListener("focus", function () {
        if (input.value.trim().length >= 2) {
          updateSuggestionsFromInput();
        }
      });

      input.addEventListener("input", function () {
        setSearchQuery(input.value, { focusResults: false });
      });

      input.addEventListener("keydown", function (event) {
        if (event.key === "Escape" && searchSuggestions) {
          searchSuggestions.classList.remove("is-visible");
          searchSuggestions.setAttribute("aria-hidden", "true");
          input.blur();
        }
      });
    });
  }

  if (headerSearchForm) {
    headerSearchForm.addEventListener("submit", function (event) {
      event.preventDefault();
      setSearchQuery(headerSearchInput ? headerSearchInput.value : "", { focusResults: true });
    });
  }

  if (headerSearchButton) {
    headerSearchButton.addEventListener("click", function () {
      setSearchQuery(headerSearchInput ? headerSearchInput.value : "", { focusResults: true });
    });
  }

  if (clearShopFiltersBtn) {
    clearShopFiltersBtn.addEventListener("click", clearFilters);
  }

  if (clearShopFiltersEmptyBtn) {
    clearShopFiltersEmptyBtn.addEventListener("click", clearFilters);
  }

  if (priceFromSelect) {
    priceFromSelect.addEventListener("change", function () {
      catalogState.minPrice = Number(priceFromSelect.value || 0);
      applyCatalogFilters({ focusResults: false });
    });
  }

  if (sortBySelect) {
    sortBySelect.addEventListener("change", function () {
      catalogState.sortBy = String(sortBySelect.value || "price-asc");
      applyCatalogFilters({ focusResults: false });
    });
  }

  window.filterProducts = function () {
    if (priceFromSelect) {
      catalogState.minPrice = Number(priceFromSelect.value || 0);
    }
    applyCatalogFilters({ focusResults: false });
  };

  window.sortProducts = function () {
    if (sortBySelect) {
      catalogState.sortBy = String(sortBySelect.value || "price-asc");
    }
    applyCatalogFilters({ focusResults: false });
  };

  document.addEventListener("click", function (event) {
    if (!searchSuggestions || !headerSearchForm) {
      return;
    }

    if (headerSearchForm.contains(event.target) || searchSuggestions.contains(event.target)) {
      return;
    }

    searchSuggestions.classList.remove("is-visible");
    searchSuggestions.setAttribute("aria-hidden", "true");
  });

  applyCatalogFilters({ focusResults: false });
  refreshCartViews().catch(function () {});

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
      .catch(function () {});
  }

  if (profileMenu && profileIcon && profilePopup) {
    profileIcon.setAttribute("aria-expanded", "false");

    profileIcon.addEventListener("click", function (event) {
      if (window.matchMedia("(max-width: 800px)").matches) {
        event.preventDefault();
        profileMenu.classList.toggle("open");
        var isOpen = profileMenu.classList.contains("open");
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

  var zoomTrigger = document.getElementById("zoomTrigger");
  var zoomModal = document.getElementById("zoomModal");
  var closeModal = document.getElementById("closeModal");
  var zoomedImage = document.getElementById("zoomedImage");

  if (zoomTrigger && zoomModal && closeModal && zoomedImage) {
    zoomTrigger.addEventListener("click", function () {
      var sourceImage = zoomTrigger.querySelector("img");
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

function showCartToast(message, type) {
  var toast = document.createElement("div");
  toast.className = "cart-toast " + (type === "error" ? "is-error" : "is-success");
  toast.textContent = message || "Hotovo";

  document.body.appendChild(toast);

  requestAnimationFrame(function () {
    toast.classList.add("show");
  });

  setTimeout(function () {
    toast.classList.remove("show");
    setTimeout(function () {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 250);
  }, 3000);
}