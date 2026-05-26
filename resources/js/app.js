document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    const wrapper = button.closest('.relative') ?? button.parentElement;
    const input = wrapper?.querySelector('[data-password-input]');

    if (!input) {
        return;
    }

    const eyeOpen = button.querySelector('[data-eye-open]');
    const eyeClosed = button.querySelector('[data-eye-closed]');

    button.addEventListener('click', () => {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        eyeOpen?.classList.toggle('hidden', isPassword);
        eyeClosed?.classList.toggle('hidden', !isPassword);
        button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
    });
});
