<style>
    :root {
        --orora-black: #000000;
        --orora-teal: #FFFFFF;
        --orora-accent: #FFFFFF;
        --orora-button: #A4D400;
        --orora-gray: #808080;
        --orora-gray-light: #CCCCCC;
    }
    .auth-page { background-color: var(--orora-black); color: var(--orora-gray-light); }
    .auth-sidebar { background-color: var(--orora-black); }
    .auth-main { background-color: var(--orora-teal); color: var(--orora-black); }
    .auth-shape {
        position: absolute;
        border-radius: 1.5rem;
        border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .auth-shape-1 { left: -2rem; top: 6rem; width: 13rem; height: 13rem; transform: rotate(12deg); }
    .auth-shape-2 { left: 8rem; top: 12rem; width: 16rem; height: 16rem; transform: rotate(-6deg); border-color: rgba(255, 255, 255, 0.08); background: rgba(255, 255, 255, 0.05); }
    .auth-shape-3 { right: -3rem; bottom: 5rem; width: 18rem; height: 18rem; transform: rotate(12deg); }
    .auth-shape-4 { right: 6rem; top: 4rem; width: 10rem; height: 10rem; transform: rotate(45deg); border-color: rgba(255, 255, 255, 0.15); }
    .auth-logo { max-width: 220px; height: auto; }
    .auth-heading { color: var(--orora-gray-light); }
    .auth-main .auth-title { color: var(--orora-black); }
    .auth-main .auth-muted { color: var(--orora-gray); font-size: 0.875rem; }
    .auth-main .auth-link { color: var(--orora-black); font-weight: 500; text-decoration: underline; text-underline-offset: 2px; }
    .auth-main .auth-link:hover { color: var(--orora-gray); }
    .auth-main .auth-input {
        width: 100%;
        border: none;
        border-bottom: 1px solid var(--orora-gray);
        background: transparent;
        padding: 0.75rem 0;
        font-size: 1rem;
        color: var(--orora-black);
        outline: none;
    }
    .auth-main .auth-input:focus { border-bottom-color: var(--orora-black); }
    .auth-main .auth-input::placeholder { color: var(--orora-gray); }
    .auth-btn-primary {
        width: 100%;
        border-radius: 0.5rem;
        background: var(--orora-button);
        color: var(--orora-black);
        padding: 0.875rem 1rem;
        font-size: 1rem;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
    }
    .auth-btn-primary:hover { background: #b8e835; }
    .auth-main .auth-btn-google {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background: transparent;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        color: var(--orora-black);
        cursor: not-allowed;
        opacity: 0.6;
    }
    @media (min-width: 1024px) {
        .auth-shell { flex-direction: row; }
    }
</style>
