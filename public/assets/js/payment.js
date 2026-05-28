document.addEventListener('DOMContentLoaded', function () {
  const steps = Array.from(document.querySelectorAll('.payment-stepper .payment-step'));
  const panels = Array.from(document.querySelectorAll('.payment-stage'));
  const paymentMethodCards = Array.from(document.querySelectorAll('.payment-method-card'));
  const paymentMethodPanels = Array.from(document.querySelectorAll('.payment-method-form'));
  const paymentMethodInputs = Array.from(document.querySelectorAll('input[name="payment_method"]'));
  const deliveryOptionCards = Array.from(document.querySelectorAll('.delivery-option-card'));
  const deliveryOptionInputs = Array.from(document.querySelectorAll('input[name="cash_delivery"]'));
  const deliveryOptionPanels = Array.from(document.querySelectorAll('.payment-delivery-details'));
  const cardNumberInput = document.querySelector('input[name="card_number"]');
  const cardExpiryInput = document.querySelector('input[name="card_expiry"]');
  const cardNumberPreview = document.querySelector('.card-digits--ghost');

  function digitsOnly(value) {
    return String(value || '').replace(/\D/g, '');
  }

  function formatCardNumber(value) {
    return digitsOnly(value)
      .slice(0, 16)
      .replace(/(.{4})/g, '$1 ')
      .trim();
  }

  function formatExpiry(value) {
    const digits = digitsOnly(value).slice(0, 4);

    if (digits.length <= 2) {
      return digits;
    }

    return digits.slice(0, 2) + '/' + digits.slice(2);
  }

  function updateCardPreview() {
    if (!cardNumberPreview || !cardNumberInput) {
      return;
    }

    const formattedNumber = formatCardNumber(cardNumberInput.value);
    cardNumberInput.value = formattedNumber;
    cardNumberPreview.textContent = formattedNumber || '•••• •••• •••• 4281';
  }

  function activateStep(stepName) {
    steps.forEach(function (step) {
      const isActive = step.dataset.stepTarget === stepName;
      step.classList.toggle('is-active', isActive);
      step.setAttribute('aria-current', isActive ? 'step' : 'false');

      if (step.dataset.stepTarget === 'address') {
        step.classList.toggle('is-done', stepName !== 'address');
      }

      if (step.dataset.stepTarget === 'payment') {
        step.classList.toggle('is-done', stepName === 'review');
      }
    });

    panels.forEach(function (panel) {
      panel.classList.toggle('is-visible', panel.dataset.stepPanel === stepName);
    });

    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function activateCashDelivery(methodName) {
    deliveryOptionInputs.forEach(function (input) {
      input.checked = input.value === methodName;
    });

    deliveryOptionCards.forEach(function (card) {
      const input = card.querySelector('input[name="cash_delivery"]');
      const isActive = Boolean(input && input.value === methodName);
      card.classList.toggle('is-active', isActive);
    });

    deliveryOptionPanels.forEach(function (panel) {
      panel.classList.toggle('is-visible', panel.dataset.deliveryPanel === methodName);
    });
  }

  function activatePaymentMethod(methodName) {
    paymentMethodInputs.forEach(function (input) {
      input.checked = input.value === methodName;
    });

    paymentMethodCards.forEach(function (card) {
      const input = card.querySelector('input[name="payment_method"]');
      const isActive = Boolean(input && input.value === methodName);
      card.classList.toggle('is-active', isActive);
      card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });

    paymentMethodPanels.forEach(function (panel) {
      panel.classList.toggle('is-visible', panel.dataset.paymentPanel === methodName);
    });

    if (methodName === 'cash') {
      const activeCash = document.querySelector('input[name="cash_delivery"]:checked');
      activateCashDelivery(activeCash ? activeCash.value : 'alzabox');
    }
  }

  if (cardNumberInput) {
    cardNumberInput.setAttribute('maxlength', '19');
    cardNumberInput.addEventListener('input', function () {
      updateCardPreview();
    });

    cardNumberInput.addEventListener('blur', function () {
      updateCardPreview();
    });
  }

  if (cardExpiryInput) {
    cardExpiryInput.setAttribute('maxlength', '5');
    cardExpiryInput.addEventListener('input', function () {
      const formattedExpiry = formatExpiry(cardExpiryInput.value);
      cardExpiryInput.value = formattedExpiry;
    });

    cardExpiryInput.addEventListener('blur', function () {
      cardExpiryInput.value = formatExpiry(cardExpiryInput.value);
    });
  }

  steps.forEach(function (step) {
    step.addEventListener('click', function () {
      activateStep(step.dataset.stepTarget);
    });
  });

  document.querySelectorAll('[data-step-next]').forEach(function (button) {
    button.addEventListener('click', function () {
      activateStep(button.dataset.stepNext);
    });
  });

  document.querySelectorAll('[data-step-prev]').forEach(function (button) {
    button.addEventListener('click', function () {
      activateStep(button.dataset.stepPrev);
    });
  });

  paymentMethodInputs.forEach(function (input) {
    input.addEventListener('change', function () {
      activatePaymentMethod(input.value);
    });
  });

  paymentMethodCards.forEach(function (card) {
    card.addEventListener('click', function () {
      const input = card.querySelector('input[name="payment_method"]');
      if (!input) {
        return;
      }

      input.checked = true;
      activatePaymentMethod(input.value);
    });
  });

  deliveryOptionInputs.forEach(function (input) {
    input.addEventListener('change', function () {
      activateCashDelivery(input.value);
    });
  });

  deliveryOptionCards.forEach(function (card) {
    card.addEventListener('click', function () {
      const input = card.querySelector('input[name="cash_delivery"]');
      if (!input) {
        return;
      }

      input.checked = true;
      activateCashDelivery(input.value);
    });
  });

  const activeStep = document.querySelector('.payment-step.is-active');
  activateStep(activeStep ? activeStep.dataset.stepTarget : 'payment');

  const activeMethod = document.querySelector('input[name="payment_method"]:checked');
  activatePaymentMethod(activeMethod ? activeMethod.value : 'card');
  updateCardPreview();
});
