  (function () {
    const authShell = document.getElementById('authShell');
    if (!authShell) {
      return;
    }

    const authTrack = authShell.querySelector('#authTrack');
    const loginPanel = authShell.querySelector('.login-panel');
    const registerPanel = authShell.querySelector('.register-panel');
    const tabs = authShell.querySelectorAll('.auth-tab, .auth-inline-switch');
    const tabButtons = authShell.querySelectorAll('.auth-tab');

    function setTrackHeight(mode) {
      if (!authTrack || !loginPanel || !registerPanel) {
        return;
      }

      const activePanel = mode === 'register' ? registerPanel : loginPanel;
      authTrack.style.height = `${activePanel.scrollHeight}px`;
    }

    function setMode(mode) {
      authShell.classList.toggle('is-register', mode === 'register');

      requestAnimationFrame(function () {
        setTrackHeight(mode);
      });

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

    window.addEventListener('resize', function () {
      setTrackHeight(authShell.classList.contains('is-register') ? 'register' : 'login');
    });

    // Keep initial height aligned with the currently active tab.
    setTrackHeight(authShell.classList.contains('is-register') ? 'register' : 'login');

    const passwordToggles = authShell.querySelectorAll('.password-toggle');

    passwordToggles.forEach((toggle) => {
      toggle.addEventListener('click', function () {
        const targetInputId = this.getAttribute('data-toggle-password');
        const input = targetInputId ? document.getElementById(targetInputId) : null;

        if (!input) {
          return;
        }

        const isVisible = input.type === 'text';
        input.type = isVisible ? 'password' : 'text';
        this.classList.toggle('is-visible', !isVisible);
        this.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
        this.setAttribute('aria-label', isVisible ? 'Zobraziť heslo' : 'Skryť heslo');
      });
    });
  })();