(() => {
    const form = document.getElementById('feed-calculator-form');
    if (!form) {
        return;
    }

    const farmSelect = document.getElementById('farm_id');
    const livestockSelect = document.getElementById('livestock_id');
    const animalSelect = document.getElementById('animal_id');
    const animalRow = document.getElementById('animal-row');
    const levelRadios = form.querySelectorAll('input[name="level"]');

    const livestockUrl = form.dataset.livestockUrl;
    const animalsUrl = form.dataset.animalsUrl;

    let preferredLivestock = livestockSelect?.dataset.selected || '';
    let preferredAnimal = animalSelect?.dataset.selected || '';

    const resetSelect = (select, placeholder) => {
        if (!select) {
            return;
        }

        select.innerHTML = '';
        select.append(new Option(placeholder, ''));
    };

    const addOptions = (select, items) => {
        for (const item of items) {
            select.append(new Option(item.label, String(item.id)));
        }
    };

    const syncAnimalRow = () => {
        const level = form.querySelector('input[name="level"]:checked')?.value || 'individual';
        const isIndividual = level === 'individual';

        if (animalRow) {
            animalRow.hidden = !isIndividual;
        }

        if (animalSelect) {
            animalSelect.required = isIndividual;
            if (!isIndividual) {
                animalSelect.value = '';
            }
        }
    };

    const loadLivestock = async () => {
        const farmId = farmSelect?.value;
        resetSelect(livestockSelect, 'Select livestock');
        resetSelect(animalSelect, 'Select animal');

        if (!farmId) {
            return;
        }

        const response = await fetch(`${livestockUrl}?farm_id=${encodeURIComponent(farmId)}`);
        const data = await response.json();
        addOptions(livestockSelect, data);

        if (preferredLivestock && data.some((item) => String(item.id) === preferredLivestock)) {
            livestockSelect.value = preferredLivestock;
        }

        preferredLivestock = '';
        await loadAnimals();
    };

    const loadAnimals = async () => {
        resetSelect(animalSelect, 'Select animal');

        if (!animalSelect || animalRow?.hidden) {
            return;
        }

        const livestockId = livestockSelect?.value;
        if (!livestockId) {
            return;
        }

        const response = await fetch(`${animalsUrl}?livestock_id=${encodeURIComponent(livestockId)}`);
        const data = await response.json();
        addOptions(animalSelect, data);

        if (preferredAnimal && data.some((item) => String(item.id) === preferredAnimal)) {
            animalSelect.value = preferredAnimal;
        }

        preferredAnimal = '';
    };

    levelRadios.forEach((radio) => {
        radio.addEventListener('change', () => {
            syncAnimalRow();
            loadAnimals();
        });
    });

    farmSelect?.addEventListener('change', () => {
        preferredLivestock = '';
        preferredAnimal = '';
        loadLivestock();
    });

    livestockSelect?.addEventListener('change', () => {
        preferredAnimal = '';
        loadAnimals();
    });

    syncAnimalRow();
    if (farmSelect?.value) {
        loadLivestock();
    }
})();
