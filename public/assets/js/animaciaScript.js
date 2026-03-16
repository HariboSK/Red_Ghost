document.addEventListener("DOMContentLoaded", () => {
  const texts = [
    "Red Ghost – kde každé sústo rozpráva príbeh ohňa !",
    "Skús naše chute klikni na OBJEDNAJ SI TU !"
  ];

  const typingSpeed = 100;
  const deletingSpeed = 50;
  const pauseTime = 5000;

  let index = 0;
  let charIndex = 0;
  let isDeleting = false;
  const element = document.getElementById("typewriter");

  function typeLoop() {
    const currentText = texts[index];

    if (isDeleting) {
      charIndex--;
      element.textContent = currentText.substring(0, charIndex);
      if (charIndex === 0) {
        isDeleting = false;
        index = (index + 1) % texts.length;
        setTimeout(typeLoop, typingSpeed);
      } else {
        setTimeout(typeLoop, deletingSpeed);
      }
    } else {
      charIndex++;
      element.textContent = currentText.substring(0, charIndex);
      if (charIndex === currentText.length) {
        setTimeout(() => {
          isDeleting = true;
          typeLoop();
        }, pauseTime);
      } else {
        setTimeout(typeLoop, typingSpeed);
      }
    }
  }

  typeLoop();
});
