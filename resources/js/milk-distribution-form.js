document.querySelectorAll('[data-milk-distribution-form]').forEach((form) => {
    const category = form.querySelector('[data-md-category]');
    const farm = form.querySelector('[data-md-farm]');
    const recipientType = form.querySelector('[data-md-recipient-type]');
    const recipientName = form.querySelector('#recipient_name');
    const otherUse = form.querySelector('#other_use');
    const nameRequiredMark = form.querySelector('[data-md-name-required]');
    const calfSelect = form.querySelector('#animal_id');
    const calfEmpty = form.querySelector('[data-md-calf-empty]');
    const calfPickFarm = form.querySelector('[data-md-calf-pick-farm]');
    const categoryRecipients = JSON.parse(form.dataset.categoryRecipients || '{}');

    if (! category || ! farm || ! recipientType) {
        return;
    }

    const sections = (name) => form.querySelectorAll(`[data-md-section="${name}"]`);

    function syncCategory() {
        const isCalf = category.value === 'fed_to_calf';
        const isOther = category.value === 'other';
        const allowed = categoryRecipients[category.value] || [];

        sections('calf').forEach((el) => { el.hidden = ! isCalf; });
        sections('other').forEach((el) => { el.hidden = ! isOther; });
        sections('person').forEach((el) => { el.hidden = isCalf; });

        if (calfSelect) {
            calfSelect.required = isCalf;
        }
        if (otherUse) {
            otherUse.required = isOther;
        }

        [...recipientType.options].forEach((option) => {
            option.hidden = ! allowed.includes(option.value);
            option.disabled = option.hidden;
        });

        if (! allowed.includes(recipientType.value) && allowed.length) {
            recipientType.value = allowed[0];
        }

        const nameRequired = ['neighbor', 'relative_visitor'].includes(category.value);
        if (recipientName) {
            recipientName.required = nameRequired && ! isCalf;
        }
        if (nameRequiredMark) {
            nameRequiredMark.hidden = ! nameRequired;
        }
    }

    function syncFarm() {
        form.querySelectorAll('[data-md-farm-filtered]').forEach((select) => {
            let visible = 0;

            [...select.options].forEach((option) => {
                if (! option.dataset.farmId) {
                    return;
                }

                const matches = farm.value !== '' && option.dataset.farmId === farm.value;
                option.hidden = ! matches;
                option.disabled = ! matches;
                visible += matches ? 1 : 0;

                if (! matches && option.selected) {
                    select.value = '';
                }
            });

            if (select === calfSelect) {
                const noFarm = farm.value === '';
                const noCalves = ! noFarm && visible === 0;

                select.disabled = noFarm || noCalves;
                select.options[0].textContent = noFarm
                    ? select.dataset.placeholderNoFarm
                    : (noCalves ? select.dataset.placeholderNone : select.dataset.placeholder);

                if (calfPickFarm) {
                    calfPickFarm.hidden = ! noFarm;
                }
                if (calfEmpty) {
                    calfEmpty.hidden = ! noCalves;
                }
            }
        });
    }

    category.addEventListener('change', syncCategory);
    farm.addEventListener('change', syncFarm);
    syncCategory();
    syncFarm();
});
