document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const wrapper = button.closest('.auth-control__wrap') ?? button.parentElement;
    const input = wrapper?.querySelector('[data-password-input]');

    if (! input) {
        return;
    }

    const eyeOpen = button.querySelector('[data-eye-open]');
    const eyeClosed = button.querySelector('[data-eye-closed]');

    button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        eyeOpen?.classList.toggle('hidden', isPassword);
        eyeClosed?.classList.toggle('hidden', ! isPassword);
        button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
});

(() => {
    const sidebar = document.querySelector('[data-dash-sidebar]');
    const overlay = document.querySelector('[data-dash-nav-overlay]');
    const toggles = document.querySelectorAll('[data-dash-nav-toggle]');
    const closers = document.querySelectorAll('[data-dash-nav-close]');

    if (! sidebar || ! overlay || ! toggles.length) {
        return;
    }

    const setOpen = (open) => {
        document.body.classList.toggle('dash-nav-open', open);
        overlay.hidden = ! open;
        toggles.forEach((btn) => {
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
        });
    };

    toggles.forEach((btn) => {
        btn.addEventListener('click', () => {
            setOpen(! document.body.classList.contains('dash-nav-open'));
        });
    });

    closers.forEach((btn) => {
        btn.addEventListener('click', () => setOpen(false));
    });

    overlay.addEventListener('click', () => setOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.matchMedia('(max-width: 900px)').matches) {
                setOpen(false);
            }
        });
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 901px)').matches) {
            setOpen(false);
        }
    });
})();
