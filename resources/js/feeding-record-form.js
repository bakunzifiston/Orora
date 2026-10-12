(() => {
    const root = document.querySelector('[data-feeding-target-form], [data-feeding-record-form]');
    if (!root) {
        return;
    }

    const farmSelect = root.querySelector('[data-feeding-farm]');
    const feedTypeSelect = root.querySelector('[data-feeding-feed-type]');
    const inventorySelect = root.querySelector('[data-feeding-inventory]');
    const scheduleSelect = root.querySelector('[data-feeding-schedule]');
    const livestockSelect = root.querySelector('[data-feeding-livestock]');
    const animalSelect = root.querySelector('[data-feeding-animal]');
    const flockSelect = root.querySelector('[data-feeding-flock]');
    const cattleTarget = root.querySelector('[data-feeding-cattle-target]');
    const flockField = root.querySelector('[data-feeding-flock-field]');
    const livestockField = root.querySelector('[data-feeding-livestock-field]');
    const animalField = root.querySelector('[data-feeding-animal-field]');
    const livestockRequired = root.querySelector('[data-feeding-livestock-required]');
    const targetRadios = root.querySelectorAll('[data-feeding-target]');

    const filterOptions = (select, predicate) => {
        if (!select) {
            return;
        }

        let keepSelection = false;

        Array.from(select.options).forEach((option) => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const matches = predicate(option);
            option.hidden = !matches;

            if (option.selected && matches) {
                keepSelection = true;
            }
        });

        if (!keepSelection) {
            select.value = '';
        }
    };

    const selectedFarmSpecies = () => {
        const option = farmSelect?.selectedOptions?.[0];

        return option?.dataset?.species || 'cattle';
    };

    const isPoultryFarm = () => selectedFarmSpecies() === 'poultry';

    const currentTarget = () =>
        root.querySelector('[data-feeding-target]:checked')?.value || 'group';

    const syncSpeciesFields = () => {
        const poultry = isPoultryFarm();

        if (cattleTarget) {
            cattleTarget.hidden = poultry;
            cattleTarget.style.display = poultry ? 'none' : 'contents';
        }

        if (flockField) {
            flockField.hidden = !poultry;
        }

        if (poultry) {
            if (livestockSelect) {
                livestockSelect.value = '';
                livestockSelect.required = false;
            }

            if (animalSelect) {
                animalSelect.value = '';
                animalSelect.required = false;
            }

            if (livestockField) {
                livestockField.hidden = true;
            }

            if (animalField) {
                animalField.hidden = true;
            }

            if (livestockRequired) {
                livestockRequired.hidden = true;
            }

            return;
        }

        if (flockSelect) {
            flockSelect.value = '';
        }

        const groupRadio = root.querySelector('[data-feeding-target][value="group"]');
        if (groupRadio && !root.querySelector('[data-feeding-target]:checked')) {
            groupRadio.checked = true;
        }

        syncTargetFields();
    };

    const syncTargetFields = () => {
        if (isPoultryFarm()) {
            return;
        }

        const target = currentTarget();
        const showAnimal = target === 'animal';

        if (livestockField) {
            livestockField.hidden = false;
        }

        if (animalField) {
            animalField.hidden = !showAnimal;
        }

        if (livestockRequired) {
            livestockRequired.hidden = false;
        }

        if (livestockSelect) {
            livestockSelect.required = true;
        }

        if (animalSelect) {
            animalSelect.required = showAnimal;
            if (!showAnimal) {
                animalSelect.value = '';
            }
        }
    };

    const filterInventory = () => {
        const farmId = farmSelect?.value || '';
        const feedTypeId = feedTypeSelect?.value || '';

        filterOptions(inventorySelect, (option) => {
            const farmOk = !farmId || option.dataset.farmId === farmId;
            const typeOk = !feedTypeId || !option.dataset.feedTypeId || option.dataset.feedTypeId === feedTypeId;

            return farmOk && typeOk;
        });
    };

    const filterByFarm = () => {
        const farmId = farmSelect?.value || '';

        filterInventory();
        filterOptions(scheduleSelect, (option) => !farmId || option.dataset.farmId === farmId);
        filterOptions(livestockSelect, (option) => !farmId || option.dataset.farmId === farmId);
        filterOptions(flockSelect, (option) => !farmId || option.dataset.farmId === farmId);
        filterAnimals();
        syncSpeciesFields();
    };

    const filterAnimals = () => {
        const farmId = farmSelect?.value || '';
        const livestockId = livestockSelect?.value || '';

        filterOptions(animalSelect, (option) => {
            const farmOk = !farmId || option.dataset.farmId === farmId;
            const livestockOk = !livestockId || option.dataset.livestockId === livestockId;

            return farmOk && livestockOk;
        });
    };

    targetRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            syncTargetFields();
            filterAnimals();
        });
    });

    farmSelect?.addEventListener('change', () => {
        filterByFarm();
    });

    feedTypeSelect?.addEventListener('change', () => {
        filterInventory();
    });

    livestockSelect?.addEventListener('change', () => {
        filterAnimals();
    });

    filterByFarm();
})();
