  (function () {
    const authShell = document.getElementById('authShell');
    if (!authShell) {
      return;
    }

    const tabs = authShell.querySelectorAll('.auth-tab, .auth-inline-switch');
    const tabButtons = authShell.querySelectorAll('.auth-tab');

    function setMode(mode) {
      authShell.classList.toggle('is-register', mode === 'register');

      tabButtons.forEach((tab) => {
        const isActive = tab.getAttribute('data-target') === mode;
        tab.classList.toggle('is-active', isActive);
        tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
      });
    }

    tabs.forEach((element) => {
      element.addEventListener('click', function () {
        setMode(this.getAttribute('data-target'));
      });
    });
  })();