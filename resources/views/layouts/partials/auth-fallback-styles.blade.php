<style>
    :root {
        --orora-black: #000000;
        --orora-sidebar: #002B2B;
        --orora-button: #A4D400;
        --orora-button-hover: #b8e835;
        --orora-gray: #808080;
        --orora-gray-light: #CCCCCC;
        --orora-surface: #ffffff;
    }

    .auth-page {
        margin: 0;
        min-height: 100vh;
        font-family: inherit;
        background: var(--orora-surface);
    }

    .auth-split {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    @media (min-width: 900px) {
        .auth-split {
            flex-direction: row;
        }
    }

    /* Left — photo + testimonial */
    .auth-visual {
        position: relative;
        flex: 1 1 58%;
        min-height: 280px;
        background-color: var(--orora-sidebar);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2rem 1.75rem;
    }

    .auth-visual::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background-image: var(--auth-bg);
        background-size: cover;
        background-position: center;
        transform: scaleX(-1);
    }

    @media (min-width: 900px) {
        .auth-visual {
            min-height: 100vh;
            padding: 2.5rem 2.5rem 2.75rem;
        }
    }

    .auth-visual__overlay {
        position: absolute;
        inset: 0;
        z-index: 1;
        background: linear-gradient(
            180deg,
            rgba(0, 43, 43, 0.55) 0%,
            rgba(0, 0, 0, 0.2) 45%,
            rgba(0, 0, 0, 0.65) 100%
        );
        pointer-events: none;
    }

    .auth-visual__quote-wrap {
        position: relative;
        z-index: 2;
        max-width: 22rem;
        margin-top: auto;
    }

    .auth-visual__quote {
        margin: 0;
        font-size: clamp(1.25rem, 2.5vw, 1.65rem);
        font-weight: 600;
        line-height: 1.35;
        color: #fff;
    }

    .auth-visual__cite {
        display: block;
        margin-top: 1.25rem;
        font-size: 0.875rem;
        font-style: normal;
        font-weight: 600;
        color: #fff;
    }

    .auth-visual__cite-role {
        display: block;
        margin-top: 0.2rem;
        font-size: 0.8125rem;
        font-weight: 400;
        color: var(--orora-gray-light);
    }

    /* Right — form */
    .auth-main {
        flex: 1 1 42%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1.5rem;
        background: var(--orora-surface);
    }

    @media (min-width: 900px) {
        .auth-main {
            min-height: 100vh;
            padding: 3rem 2rem;
        }
    }

    .auth-main__inner {
        width: 100%;
        max-width: 380px;
    }

    .auth-form-brand {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .auth-form-brand__logo {
        display: block;
        max-width: 160px;
        width: 100%;
        height: auto;
    }

    .auth-form-title {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--orora-black);
        letter-spacing: -0.02em;
        text-align: center;
    }

    .auth-form-subtitle {
        margin: 0.4rem 0 0;
        font-size: 0.875rem;
        line-height: 1.45;
        color: var(--orora-gray);
        text-align: center;
    }

    .auth-alert {
        margin-top: 1.25rem;
        padding: 0.7rem 0.9rem;
        border-radius: 0.5rem;
        background: #fef2f2;
        border-left: 3px solid #ef4444;
        font-size: 0.8125rem;
        color: #b91c1c;
    }

    .auth-alert ul {
        margin: 0;
        padding-left: 1rem;
    }

    .auth-form {
        margin-top: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .auth-form-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: -0.15rem;
    }

    .auth-control label {
        display: block;
        margin-bottom: 0.4rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--orora-black);
    }

    .auth-required {
        color: #dc2626;
    }

    .auth-control__wrap {
        position: relative;
    }

    .auth-control__input {
        width: 100%;
        box-sizing: border-box;
        padding: 0.8rem 2.75rem 0.8rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.625rem;
        font-size: 0.9375rem;
        color: var(--orora-black);
        background: var(--orora-surface);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .auth-control__input:focus {
        border-color: var(--orora-sidebar);
        box-shadow: 0 0 0 3px rgba(0, 43, 43, 0.1);
    }

    .auth-control__input::placeholder {
        color: #9ca3af;
    }

    .auth-control__toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        padding: 0.2rem;
        color: var(--orora-gray);
        cursor: pointer;
        display: flex;
    }

    .auth-forgot-link {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--orora-sidebar);
        text-decoration: none;
    }

    .auth-forgot-link:hover {
        text-decoration: underline;
    }

    .auth-forgot-link--disabled {
        opacity: 0.45;
        pointer-events: none;
        cursor: not-allowed;
    }

    .auth-toggle {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        cursor: pointer;
        user-select: none;
    }

    .auth-toggle__input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .auth-toggle__track {
        position: relative;
        flex-shrink: 0;
        width: 2.75rem;
        height: 1.5rem;
        border-radius: 9999px;
        background: #e5e7eb;
        transition: background 0.2s;
    }

    .auth-toggle__track::after {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 1.125rem;
        height: 1.125rem;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
    }

    .auth-toggle__input:checked + .auth-toggle__track {
        background: var(--orora-button);
    }

    .auth-toggle__input:checked + .auth-toggle__track::after {
        transform: translateX(1.25rem);
    }

    .auth-toggle__input:focus-visible + .auth-toggle__track {
        box-shadow: 0 0 0 3px rgba(164, 212, 0, 0.45);
    }

    .auth-toggle__label {
        font-size: 0.8125rem;
        color: var(--orora-gray);
    }

    .auth-btn-primary {
        width: 100%;
        margin-top: 0.25rem;
        padding: 0.85rem 1.25rem;
        border: none;
        border-radius: 0.625rem;
        background: var(--orora-button);
        color: var(--orora-black);
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s;
    }

    .auth-btn-primary:hover {
        background: var(--orora-button-hover);
    }

    .auth-form-footer {
        margin-top: 1.5rem;
        text-align: center;
        font-size: 0.875rem;
        color: var(--orora-gray);
    }

    .auth-form-footer a {
        color: var(--orora-sidebar);
        font-weight: 700;
        text-decoration: none;
    }

    .auth-form-footer a:hover {
        text-decoration: underline;
    }

    .hidden {
        display: none !important;
    }
</style>
