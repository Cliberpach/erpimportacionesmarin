<style>
/* Lightbox container */
#lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    z-index: 999999;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.imgShowLightBox{
    cursor: pointer;
}

/* Imagen dentro del lightbox */
#lightbox_img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
    transition: transform 0.2s ease;
    cursor: zoom-in;
    touch-action: none; /* importante para pointer events/touch */
    transform-origin: center center;
    will-change: transform;
}

/* Botón de cierre */
#close_lightbox {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 30px;
    font-weight: bold;
    color: white;
    cursor: pointer;
    user-select: none;
}
</style>

<!-- Lightbox -->
<div id="lightbox">
    <span id="close_lightbox">&times;</span>
    <img id="lightbox_img" alt="">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox_img');
    const closeBtn = document.getElementById('close_lightbox');

    let zoomed = false;
    let scale = 1;
    const ZOOM_SCALE = 2;
    let translateX = 0;
    let translateY = 0;

    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let startTranslateX = 0;
    let startTranslateY = 0;

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('imgShowLightBox')) {
            lightboxImg.src = e.target.src;
            lightbox.style.display = 'flex';
            setTimeout(() => { lightbox.style.opacity = '1'; }, 10);

            zoomed = false;
            scale = 1;
            translateX = 0;
            translateY = 0;
            applyTransform();
            lightboxImg.style.cursor = 'zoom-in';
        }
    });

    closeBtn.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', function(event) {
        if (event.target === this) closeLightbox();
    });

    function closeLightbox() {
        lightbox.style.opacity = '0';
        setTimeout(() => { lightbox.style.display = 'none'; }, 500);
    }

    // Click = zoom incremental manteniendo posición
    lightboxImg.addEventListener('click', function(e) {
        if (isDragging) return;

        if (scale === 1) {
            // Primer zoom
            const rect = lightboxImg.getBoundingClientRect();
            const offsetX = (e.clientX - rect.left) - rect.width / 2;
            const offsetY = (e.clientY - rect.top) - rect.height / 2;

            scale = ZOOM_SCALE;
            translateX = -offsetX * (scale - 1);
            translateY = -offsetY * (scale - 1);

            zoomed = true;
            lightboxImg.style.cursor = 'grab';
        } else {
            // Zoom out
            scale = 1;
            translateX = 0;
            translateY = 0;
            zoomed = false;
            lightboxImg.style.cursor = 'zoom-in';
        }

        // Limitar para que no se salga
        const bounds = computeBounds();
        translateX = clamp(translateX, -bounds.maxX, bounds.maxX);
        translateY = clamp(translateY, -bounds.maxY, bounds.maxY);

        applyTransform();
    });

    lightboxImg.addEventListener('pointerdown', function(e) {
        if (!zoomed) return;
        isDragging = true;
        lightboxImg.setPointerCapture(e.pointerId);
        startX = e.clientX;
        startY = e.clientY;
        startTranslateX = translateX;
        startTranslateY = translateY;
        lightboxImg.style.transition = 'none';
        lightboxImg.style.cursor = 'grabbing';
    });

    lightboxImg.addEventListener('pointermove', function(e) {
        if (!isDragging) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;

        translateX = startTranslateX + dx;
        translateY = startTranslateY + dy;

        const bounds = computeBounds();
        translateX = clamp(translateX, -bounds.maxX, bounds.maxX);
        translateY = clamp(translateY, -bounds.maxY, bounds.maxY);

        applyTransform();
    });

    lightboxImg.addEventListener('pointerup', function(e) {
        if (!isDragging) return;
        isDragging = false;
        try { lightboxImg.releasePointerCapture(e.pointerId); } catch(_) {}
        lightboxImg.style.transition = '';
        lightboxImg.style.cursor = 'grab';
    });

    lightboxImg.addEventListener('pointercancel', function(e) {
        if (!isDragging) return;
        isDragging = false;
        try { lightboxImg.releasePointerCapture(e.pointerId); } catch(_) {}
        lightboxImg.style.transition = '';
        lightboxImg.style.cursor = 'grab';
    });

    lightboxImg.addEventListener('dragstart', (e) => e.preventDefault());

    function applyTransform() {
        lightboxImg.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }

    function clamp(val, min, max) {
        return Math.max(min, Math.min(max, val));
    }

    function computeBounds() {
        const containerRect = lightbox.getBoundingClientRect();
        const baseWidth = lightboxImg.clientWidth;
        const baseHeight = lightboxImg.clientHeight;
        const scaledWidth = baseWidth * scale;
        const scaledHeight = baseHeight * scale;
        const maxX = Math.max(0, (scaledWidth - containerRect.width) / 2);
        const maxY = Math.max(0, (scaledHeight - containerRect.height) / 2);
        return { maxX, maxY };
    }

    window.addEventListener('resize', () => {
        if (!zoomed) return;
        const bounds = computeBounds();
        translateX = clamp(translateX, -bounds.maxX, bounds.maxX);
        translateY = clamp(translateY, -bounds.maxY, bounds.maxY);
        applyTransform();
    });

    // Zoom con rueda
    lightboxImg.addEventListener('wheel', function(e) {
        if (!lightbox.style.display || lightbox.style.display === 'none') return;
        e.preventDefault();
        const delta = -e.deltaY || e.wheelDelta;
        const factor = delta > 0 ? 1.1 : 0.9;
        let newScale = scale * factor;
        newScale = Math.max(1, Math.min(4, newScale));

        if (newScale === 1) {
            scale = 1;
            translateX = 0;
            translateY = 0;
            zoomed = false;
            lightboxImg.style.cursor = 'zoom-in';
            applyTransform();
            return;
        }

        const rect = lightboxImg.getBoundingClientRect();
        const offsetX = (e.clientX - rect.left) - rect.width / 2;
        const offsetY = (e.clientY - rect.top) - rect.height / 2;
        translateX = (translateX - offsetX) * (newScale / scale) + offsetX;
        translateY = (translateY - offsetY) * (newScale / scale) + offsetY;

        scale = newScale;
        zoomed = scale > 1;
        lightboxImg.style.cursor = 'grab';

        const bounds = computeBounds();
        translateX = clamp(translateX, -bounds.maxX, bounds.maxX);
        translateY = clamp(translateY, -bounds.maxY, bounds.maxY);

        applyTransform();
    }, { passive: false });
});
</script>
