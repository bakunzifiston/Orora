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

const livestockForm = document.querySelector('[data-livestock-form]');
const farmSelect = livestockForm?.querySelector('[data-livestock-farm]');

if (livestockForm && farmSelect) {
    const catalogs = JSON.parse(livestockForm.getAttribute('data-catalogs') || '{}');
    const groups = {
        livestock_types: livestockForm.querySelector('[name="livestock_types[]"]')?.closest('[data-checkbox-group]'),
        herd_groups: livestockForm.querySelector('[name="herd_groups[]"]')?.closest('[data-checkbox-group]'),
        production_purposes: livestockForm.querySelector('[name="production_purposes[]"]')?.closest('[data-checkbox-group]'),
        farming_methods: livestockForm.querySelector('[name="farming_methods[]"]')?.closest('[data-checkbox-group]'),
        feeding_methods: livestockForm.querySelector('[name="feeding_methods[]"]')?.closest('[data-checkbox-group]'),
    };

    function renderOptions(group, options, selected) {
        if (! group) {
            return;
        }

        const grid = group.querySelector('.dash-checkbox-grid');
        const name = group.querySelector('input[type="checkbox"]')?.getAttribute('name');

        if (! grid || ! name) {
            return;
        }

        grid.innerHTML = options.map((option) => {
            const checked = selected.includes(option) ? 'checked' : '';
            const otherAttr = option === 'Other' ? ' data-other-trigger' : '';

            return `<label class="dash-checkbox"><input type="checkbox" name="${name}" value="${option}" ${checked}${otherAttr}><span>${option}</span></label>`;
        }).join('');

        group.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const otherInputWrap = group.querySelector('[data-other-input]');
                const otherField = otherInputWrap?.querySelector('input, textarea');
                const otherChecked = group.querySelector('[data-other-trigger]:checked');

                if (! otherInputWrap || ! otherField) {
                    return;
                }

                otherInputWrap.hidden = ! otherChecked;
                otherField.required = Boolean(otherChecked);
            });
        });
    }

    farmSelect.addEventListener('change', () => {
        const catalog = catalogs[farmSelect.value];

        if (! catalog) {
            return;
        }

        renderOptions(groups.livestock_types, catalog.livestock_types || [], []);
        renderOptions(groups.herd_groups, catalog.herd_groups || [], []);
        renderOptions(groups.production_purposes, catalog.production_purposes || [], []);
        renderOptions(groups.farming_methods, catalog.farming_methods || [], []);
        renderOptions(groups.feeding_methods, catalog.feeding_methods || [], []);
    });
}
