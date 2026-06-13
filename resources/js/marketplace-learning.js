document.addEventListener('DOMContentLoaded', () => {
    initLearnFilterToggle();
    initCopyLink();
});

function initLearnFilterToggle() {
    const toggle = document.querySelector('[data-learn-filters-toggle]');
    const sidebar = document.getElementById('learn-filters');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
        const open = sidebar.classList.toggle('is-open');
        toggle.textContent = open ? 'Hide Filters' : 'Show Filters';
    });
}

function initCopyLink() {
    document.querySelectorAll('[data-copy-link]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const root = btn.closest('[data-share-url]');
            const url = root?.dataset.shareUrl;
            if (!url || !navigator.clipboard) return;
            await navigator.clipboard.writeText(url);
            btn.textContent = 'Copied!';
        });
    });
}
