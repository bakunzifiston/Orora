/**
 * Orora Farm landing page interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    initStickyNav();
    initMobileNav();
    initCountUp();
    initTestimonials();
    initReveal();
});

function initReveal() {
    const nodes = document.querySelectorAll('[data-lp-reveal]');
    if (!nodes.length) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        nodes.forEach((node) => node.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.14, rootMargin: '0px 0px -6% 0px' }
    );

    nodes.forEach((node) => observer.observe(node));
}
function initStickyNav() {
    const nav = document.querySelector('[data-lp-nav]');
    if (!nav) return;

    const onScroll = () => {
        nav.classList.toggle('is-scrolled', window.scrollY > 8);
    };

    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function initMobileNav() {
    const toggle = document.querySelector('[data-lp-nav-toggle]');
    const menu = document.querySelector('[data-lp-nav-mobile]');
    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        const isOpen = !menu.hidden;
        menu.hidden = isOpen;
        toggle.setAttribute('aria-expanded', String(!isOpen));
        toggle.setAttribute('aria-label', isOpen ? 'Open menu' : 'Close menu');
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            menu.hidden = true;
            toggle.setAttribute('aria-expanded', 'false');
        });
    });
}

function initCountUp() {
    const section = document.querySelector('[data-lp-stats]');
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

function initTestimonials() {
    const root = document.querySelector('[data-lp-testimonials]');
    if (!root) return;

    const slides = Array.from(root.querySelectorAll('[data-testimonial-slide]'));
    const dots = Array.from(root.querySelectorAll('[data-testimonial-dot]'));
    const prev = root.querySelector('[data-testimonial-prev]');
    const next = root.querySelector('[data-testimonial-next]');

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

    prev?.addEventListener('click', () => show(current - 1));
    next?.addEventListener('click', () => show(current + 1));

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            show(parseInt(dot.dataset.testimonialDot || '0', 10));
        });
    });

    let timer = setInterval(() => show(current + 1), 7000);

    root.addEventListener('mouseenter', () => clearInterval(timer));
    root.addEventListener('mouseleave', () => {
        timer = setInterval(() => show(current + 1), 7000);
    });
}
