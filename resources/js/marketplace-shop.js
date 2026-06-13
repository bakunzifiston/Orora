document.addEventListener('DOMContentLoaded', () => {
    initFilterToggle();
    initGallery();
    initShare();
});

function initFilterToggle() {
    const toggle = document.querySelector('[data-shop-filters-toggle]');
    const sidebar = document.getElementById('shop-filters');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('is-open');
        toggle.textContent = open ? 'Hide Filters' : 'Show Filters';
    });
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
                btn.textContent = '✓ Link copied';
            }
        });
    });
}
