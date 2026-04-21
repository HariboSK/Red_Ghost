const navLinks = document.querySelectorAll(".nav-menu .nav-link");
const menuOpenButton = document.querySelector("#menu-open-button");
const menuCloseButton = document.querySelector("#menu-close-button");

if (menuOpenButton) {
  menuOpenButton.addEventListener("click", () => {
    //Toggle mobile menu visibility
    document.body.classList.toggle("show-mobile-menu");
  });
}

// Close menu when the close button is clicked
if (menuCloseButton && menuOpenButton) {
  menuCloseButton.addEventListener("click", () => menuOpenButton.click());
}

// Close menu when the nav link is clicked
if (menuOpenButton) {
  navLinks.forEach((link) => {
    link.addEventListener("click", () => menuOpenButton.click());
  });
}

//Initialize Swiper
if (
  typeof Swiper !== "undefined" &&
  document.querySelector(".slider-wrapper")
) {
  const swiper = new Swiper(".slider-wrapper", {
    loop: true,
    grabCursor: true,
    spaceBetween: 25,

    pagination: {
      el: ".swiper-pagination",
      clickable: true,
      dynamicBullets: true,
    },

    // Navigation arrows
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },

    // Responsive breakpoints
    breakpoints: {
      0: {
        slidesPerView: 1,
      },
      768: {
        slidesPerView: 2,
      },
      1024: {
        slidesPerView: 3,
      },
    },
  });
}

function showConfirmation() {
  const box = document.getElementById("confirmBox");
  box.style.display = "block";
  setTimeout(() => {
    box.style.display = "none";
  }, 3000);
}

const sendStatusOverlay = document.getElementById("sendStatusOverlay");
if (sendStatusOverlay) {
  setTimeout(() => {
    sendStatusOverlay.style.display = "none";
  }, 5000);
}

const dashboardPanels = document.querySelectorAll("[data-dashboard-panel]");
const dashboardSidebarLinks = document.querySelectorAll(
  '.admin-sidebar .sidebar-link[href^="#"]',
);

if (dashboardPanels.length > 0 && dashboardSidebarLinks.length > 0) {
  const activateDashboardPanel = (panelId) => {
    dashboardPanels.forEach((panel) => {
      panel.classList.toggle("is-active", panel.id === panelId);
    });

    dashboardSidebarLinks.forEach((link) => {
      link.classList.toggle(
        "active",
        link.getAttribute("href") === `#${panelId}`,
      );
    });
  };

  dashboardSidebarLinks.forEach((link) => {
    link.addEventListener("click", (event) => {
      const targetId = link.getAttribute("href")?.slice(1);
      if (!targetId) {
        return;
      }

      event.preventDefault();
      activateDashboardPanel(targetId);
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });
  });

  const initialPanel = window.location.hash.replace("#", "") || "overview";
  activateDashboardPanel(
    document.getElementById(initialPanel) ? initialPanel : "overview",
  );
}
