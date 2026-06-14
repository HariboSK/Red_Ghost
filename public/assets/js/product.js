document.addEventListener('DOMContentLoaded', function () {
    const trigger = document.querySelector('.zoom-trigger');
    const modal = document.getElementById('zoomModal');
    const modalImg = modal ? modal.querySelector('img') : null;
    const closeBtn = document.getElementById('closeZoomModal');

    if (!trigger) console.error("Chyba: Nenašiel som .zoom-trigger");
    if (!modal) console.error("Chyba: Nenašiel som #zoomModal");

    if (trigger && modal && modalImg) {
        trigger.addEventListener('click', function (e) {
            console.log("Kliknutie zachytené!");
            const imgSrc = trigger.getAttribute('data-zoom-image');
            
            if (imgSrc) {
                modalImg.src = imgSrc;
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
                console.log("Modal otvorený s obrázkom:", imgSrc);
            } else {
                console.error("Chýba atribút data-zoom-image v triggeri!");
            }
        });
    }

    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');
        });
    }
});