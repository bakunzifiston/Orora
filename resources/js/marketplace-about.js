/**
 * Orora Farm About page interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    initAboutCountUp();
    initAboutTestimonials();
});

function initAboutCountUp() {
    const section = document.querySelector('[data-ab-stats]');
    if (!section) return;

    const elements = section.querySelectorAll('[data-count-up]');
    if (!elements.length) return;

    let animated = false;

    const animate = () => {
        if (animated) return;
        animated = true;

        elements.forEach((el) => {
            const target = parseInt(el.dataset.target || '0', 10);
            const suffix = el.dataset.suffix || '';
            const duration = 1400;
            const start = performance.now();

            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const value = Math.round(target * eased);
                el.textContent = value.toLocaleString() + suffix;

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            };

            requestAnimationFrame(tick);
        });
    };

    const observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                animate();
                observer.disconnect();
            }
        },
        { threshold: 0.35 }
    );

    observer.observe(section);
}

function initAboutTestimonials() {
    const root = document.querySelector('[data-ab-testimonials]');
    if (!root) return;

    const slides = Array.from(root.querySelectorAll('[data-testimonial-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-testimonial-dot]'));

    if (!slides.length) return;

    let current = 0;

    const show = (index) => {
        current = (index + slides.length) % slides.length;

        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === current);
        });

        dots.forEach((dot, i) => {
            dot.classList.toggle('is-active', i === current);
        });
    };

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            show(parseInt(dot.dataset.testimonialDot || '0', 10));
        });
    });

    let timer = setInterval(() => show(current + 1), 5000);

    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', () => {
        timer = setInterval(() => show(current + 1), 5000);
    });
}
