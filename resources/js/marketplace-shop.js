document.addEventListener('DOMContentLoaded', () => {
    initFilterDrawer();
    initDistrictCombobox();
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

        if (open) {
            closeBtn?.focus();
        } else if (sidebar.contains(document.activeElement)) {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => {
        setOpen(!sidebar.classList.contains('is-open'));
    });

    closeBtn?.addEventListener('click', () => setOpen(false));
    backdrop?.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('is-open')) setOpen(false);
    });
}

function initDistrictCombobox() {
    const wrap = document.querySelector('[data-shop-combobox]');
    const select = wrap?.querySelector('select');
    if (!select) return;

    const options = [...select.options].map((option) => ({ value: option.value, label: option.textContent.trim() }));
    const allLabel = options.find((option) => option.value === '')?.label ?? 'All';
    const listId = `${select.id}-listbox`;

    const input = document.createElement('input');
    input.type = 'text';
    input.id = `${select.id}-search`;
    input.className = 'shop-combobox__input';
    input.placeholder = allLabel;
    input.autocomplete = 'off';
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-expanded', 'false');
    input.setAttribute('aria-controls', listId);

    const list = document.createElement('ul');
    list.id = listId;
    list.className = 'shop-combobox__list';
    list.setAttribute('role', 'listbox');
    list.hidden = true;

    wrap.classList.add('shop-combobox');
    select.classList.add('shop-combobox__native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    wrap.append(input, list);
    document.querySelector(`label[for="${select.id}"]`)?.setAttribute('for', input.id);

    let matches = [];
    let active = -1;

    const selectedLabel = () => (select.value ? select.selectedOptions[0]?.textContent.trim() ?? '' : '');

    const render = () => {
        const term = input.value.trim().toLowerCase();
        matches = term ? options.filter((option) => option.value !== '' && option.label.toLowerCase().includes(term)) : options;
        list.innerHTML = '';

        matches.forEach((option, index) => {
            const item = document.createElement('li');
            item.id = `${listId}-${index}`;
            item.className = 'shop-combobox__option';
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', option.value === select.value ? 'true' : 'false');
            item.textContent = option.label;
            item.classList.toggle('is-active', index === active);
            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
                choose(option);
            });
            list.appendChild(item);
        });

        if (!matches.length) {
            const empty = document.createElement('li');
            empty.className = 'shop-combobox__empty';
            empty.textContent = 'No matching district';
            list.appendChild(empty);
        }

        input.setAttribute('aria-activedescendant', active >= 0 ? `${listId}-${active}` : '');
        list.children[active]?.scrollIntoView({ block: 'nearest' });
    };

    const open = () => {
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        render();
    };

    const close = () => {
        list.hidden = true;
        active = -1;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        input.value = selectedLabel();
    };

    const choose = (option) => {
        select.value = option.value;
        close();
    };

    input.value = selectedLabel();

    input.addEventListener('focus', () => {
        input.select();
        open();
    });
    input.addEventListener('click', open);
    input.addEventListener('input', () => {
        active = 0;
        open();
    });
    input.addEventListener('blur', close);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (list.hidden) open();
            if (!matches.length) return;
            const step = event.key === 'ArrowDown' ? 1 : -1;
            active = (active + step + matches.length) % matches.length;
            render();
        } else if (event.key === 'Enter' && !list.hidden) {
            event.preventDefault();
            if (matches[active]) choose(matches[active]);
        } else if (event.key === 'Escape' && !list.hidden) {
            event.stopPropagation();
            close();
        }
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
        const reveal = () => {
            img.classList.remove('is-loading');
            img.classList.add('is-loaded');
        };

        if (img.complete && img.naturalWidth > 0) {
            reveal();
            return;
        }

        img.classList.add('is-loading');
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
