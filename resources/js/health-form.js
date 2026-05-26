document.querySelectorAll('[data-health-form]').forEach((root) => {
    const farmSelect = root.querySelector('[data-health-farm]');
    const animalSelect = root.querySelector('[data-health-animal]');
    const healthStatusSelect = root.querySelector('[data-health-status]');

    function filterAnimals() {
        if (! farmSelect || ! animalSelect) {
            return;
        }

        const farmId = farmSelect.value;
        let hasVisibleSelection = false;

        Array.from(animalSelect.options).forEach((option) => {
            if (! option.value) {
                option.hidden = false;
                return;
            }

            const matches = ! farmId || option.dataset.farmId === farmId;
            option.hidden = ! matches;

            if (option.selected && matches) {
                hasVisibleSelection = true;
            }
        });

        if (! hasVisibleSelection) {
            animalSelect.value = '';
        }
    }

    function syncHealthStatusFromAnimal() {
        if (! animalSelect || ! healthStatusSelect) {
            return;
        }

        const selected = animalSelect.selectedOptions[0];

        if (selected?.dataset.healthStatus) {
            healthStatusSelect.value = selected.dataset.healthStatus;
        }
    }

    farmSelect?.addEventListener('change', () => {
        filterAnimals();
        syncHealthStatusFromAnimal();
    });

    animalSelect?.addEventListener('change', syncHealthStatusFromAnimal);

    filterAnimals();
});
