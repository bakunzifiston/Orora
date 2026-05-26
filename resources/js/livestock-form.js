document.querySelectorAll('[data-checkbox-group]').forEach((group) => {
    const otherInputWrap = group.querySelector('[data-other-input]');
    const otherField = otherInputWrap?.querySelector('input, textarea');

    function syncOtherField() {
        const otherChecked = group.querySelector('[data-other-trigger]:checked');

        if (! otherInputWrap || ! otherField) {
            return;
        }

        if (otherChecked) {
            otherInputWrap.hidden = false;
            otherField.required = true;
        } else {
            otherInputWrap.hidden = true;
            otherField.required = false;
            otherField.value = '';
        }
    }

    group.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.addEventListener('change', syncOtherField);
    });

    syncOtherField();
});
