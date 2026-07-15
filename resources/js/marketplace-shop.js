document.addEventListener('DOMContentLoaded', () => {
    initFilterDrawer();
    initLoadingStates();
    initImageReveal();
    initStickyCategories();
    initGallery();
    initShare();
});

function initFilterDrawer() {
    const toggle = document.querySelector('[data-shop-filters-toggle]');
    const sidebar = document.querySelector('[data-shop-filters]');
    const backdrop = document.querySelector('[data-shop-filters-backdrop]');
    const closeBtn = document.querySelector('[data-shop-filters-close]');

    if (!toggle || !sidebar) return;

    const setOpen = (open) => {
        sidebar.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('shop-filters-open', open);

        if (backdrop) {
            backdrop.hidden = !open;
            backdrop.classList.toggle('is-visible', open);
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(!sidebar.classList.contains('is-open'));
    });

    closeBtn?.addEventListener('click', () => setOpen(false));
    backdrop?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
}

function initLoadingStates() {
    const results = document.querySelector('[data-shop-results]');
    const skeleton = document.querySelector('[data-shop-skeleton]');
    if (!results || !skeleton) return;

    const startLoading = () => {
        results.classList.add('is-loading');
        skeleton.hidden = false;
        window.scrollTo({ top: Math.max(results.offsetTop - 120, 0), behavior: 'smooth' });
    };

    document.querySelectorAll('[data-shop-form]').forEach((form) => {
        form.addEventListener('submit', () => startLoading());
    });

    document.querySelectorAll('[data-shop-nav]').forEach((link) => {
        link.addEventListener('click', () => startLoading());
    });

    document.querySelectorAll('.shop-chip:not(.shop-chip--static), .shop-pager__page, .shop-pager__btn:not(.is-disabled)').forEach((link) => {
        link.addEventListener('click', () => startLoading());
    });
}

function initImageReveal() {
    document.querySelectorAll('[data-shop-img]').forEach((img) => {
        const reveal = () => img.classList.add('is-loaded');

        if (img.complete && img.naturalWidth > 0) {
            reveal();
            return;
        }

        img.addEventListener('load', reveal, { once: true });
        img.addEventListener('error', reveal, { once: true });
    });
}

function initStickyCategories() {
    const el = document.querySelector('.shop-categories');
    if (!el || typeof IntersectionObserver === 'undefined') return;

    const sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    sentinel.style.cssText = 'height:1px;margin:0;padding:0;';
    el.parentNode.insertBefore(sentinel, el);

    const observer = new IntersectionObserver(
        ([entry]) => {
            el.classList.toggle('is-stuck', !entry.isIntersecting);
        },
        { threshold: [1] }
    );

    observer.observe(sentinel);
}

function initGallery() {
    const gallery = document.querySelector('[data-shop-gallery]');
    if (!gallery) return;

    const main = gallery.querySelector('[data-gallery-main]');
    const thumbs = gallery.querySelectorAll('[data-gallery-thumb]');

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            if (main) main.src = thumb.dataset.galleryThumb;
            thumbs.forEach((t) => t.classList.remove('is-active'));
            thumb.classList.add('is-active');
        });
    });
}

function initShare() {
    document.querySelectorAll('[data-share-url]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const url = btn.dataset.shareUrl;
            if (navigator.share) {
                try {
                    await navigator.share({ title: document.title, url });
                } catch (_) {}
            } else if (navigator.clipboard) {
                await navigator.clipboard.writeText(url);
                const original = btn.textContent;
                btn.textContent = 'Copied';
                setTimeout(() => {
                    btn.textContent = original;
                }, 1600);
            }
        });
    });
}
