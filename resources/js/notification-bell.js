document.querySelectorAll('[data-notification-bell]').forEach((bell) => {
    const toggle = bell.querySelector('[data-bell-toggle]');
    const panel = bell.querySelector('[data-bell-panel]');
    const count = bell.querySelector('[data-bell-count]');
    const readAll = bell.querySelector('[data-bell-read-all]');

    if (! toggle || ! panel) {
        return;
    }

    const setOpen = (open) => {
        panel.hidden = ! open;
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        bell.classList.toggle('is-open', open);
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        setOpen(panel.hidden);
    });

    document.addEventListener('click', (event) => {
        if (! panel.hidden && ! bell.contains(event.target)) {
            setOpen(false);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && ! panel.hidden) {
            setOpen(false);
            toggle.focus();
        }
    });

    const showUnread = (unread) => {
        if (count) {
            count.hidden = unread === 0;
            count.textContent = unread > 99 ? '99+' : String(unread);
        }
        if (readAll) {
            readAll.hidden = unread === 0;
        }
        toggle.setAttribute('aria-label', unread === 0 ? 'Notifications' : `Notifications, ${unread} unread`);
    };

    readAll?.addEventListener('submit', async (event) => {
        event.preventDefault();

        try {
            const response = await fetch(readAll.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': readAll.querySelector('input[name="_token"]')?.value ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (! response.ok) {
                throw new Error(String(response.status));
            }

            const body = await response.json();
            bell.querySelectorAll('[data-bell-item].is-unread').forEach((item) => item.classList.remove('is-unread'));
            showUnread(Number(body.unread ?? 0));
        } catch {
            readAll.submit();
        }
    });
});
