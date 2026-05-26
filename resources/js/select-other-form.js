document.querySelectorAll('[data-select-other]').forEach((wrap) => {
    const select = wrap.querySelector('[data-select-other-trigger]');
    const otherWrap = wrap.querySelector('[data-other-input]');
    const otherField = otherWrap?.querySelector('input, textarea');

    if (! select || ! otherWrap || ! otherField) {
        return;
    }

    function syncOtherField() {
        if (select.value === 'Other') {
            otherWrap.hidden = false;
            otherField.required = true;
        } else {
            otherWrap.hidden = true;
            otherField.required = false;

            if (select.value !== 'Other') {
                otherField.value = '';
            }
        }
    }

    select.addEventListener('change', syncOtherField);
    syncOtherField();
});
