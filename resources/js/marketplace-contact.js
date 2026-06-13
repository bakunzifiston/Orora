/**
 * Orora Farm Contact page interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    initContactFaq();
});

function initContactFaq() {
    const root = document.querySelector('[data-ct-faq]');
    if (!root) return;

    const items = Array.from(root.querySelectorAll('.ct-faq__item'));

    items.forEach((item) => {
        const toggle = item.querySelector('[data-faq-toggle]');
        if (!toggle) return;

        toggle.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            items.forEach((other) => {
                other.classList.remove('is-open');
                other.querySelector('[data-faq-toggle]')?.setAttribute('aria-expanded', 'false');
            });

            if (!isOpen) {
                item.classList.add('is-open');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });
    });
}
