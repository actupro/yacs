/**
 * Forest Parallax Layout JS
 * @see layouts/layout_as_2col_forest_parallax.php
 */
document.addEventListener('DOMContentLoaded', function() {
    const layout = document.querySelector('.yacs-parallax');
    if(!layout) return;

    // Simple Parallax Effect
    window.addEventListener('scroll', function() {
        const scrollY = window.scrollY;
        const bgPos = -scrollY * 0.2;
        layout.style.backgroundPositionY = bgPos + 'px';
        
        // Cards effect
        const cards = layout.querySelectorAll('.card');
        cards.forEach((card, index) => {
            const rect = card.getBoundingClientRect();
            if(rect.top < window.innerHeight) {
                const delay = index % 2 ? 0.1 : 0.05;
                card.style.transform = `translateY(${rect.top * delay}px)`;
            }
        });
    });

    // Load i18n if available
    if(typeof i18n !== 'undefined') {
        i18n.bind('layouts');
    }
});
