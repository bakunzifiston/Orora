function formatAgeFromBirthDate(value) {
    if (! value) {
        return '';
    }

    const birth = new Date(`${value}T00:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (Number.isNaN(birth.getTime()) || birth > today) {
        return '';
    }

    let years = today.getFullYear() - birth.getFullYear();
    let months = today.getMonth() - birth.getMonth();
    let days = today.getDate() - birth.getDate();

    if (days < 0) {
        months -= 1;
        const previousMonth = new Date(today.getFullYear(), today.getMonth(), 0);
        days += previousMonth.getDate();
    }

    if (months < 0) {
        years -= 1;
        months += 12;
    }

    if (years > 0 && months > 0) {
        return `${years}y ${months}m`;
    }

    if (years > 0) {
        return `${years} year${years === 1 ? '' : 's'}`;
    }

    if (months > 0) {
        return `${months} month${months === 1 ? '' : 's'}`;
    }

    return `${days} day${days === 1 ? '' : 's'}`;
}

document.querySelectorAll('[data-animal-form]').forEach((root) => {
    const farmSelect = root.querySelector('[data-animal-farm]');
    const livestockSelect = root.querySelector('[data-animal-livestock]');
    const birthDateInput = root.querySelector('[data-birth-date]');
    const ageDisplay = root.querySelector('[data-age-display]');
    const photoInput = root.querySelector('[data-photo-input]');
    const photoPreview = root.querySelector('[data-photo-preview]');

    function filterLivestockOptions() {
        if (! farmSelect || ! livestockSelect) {
            return;
        }

        const farmId = farmSelect.value;
        let hasVisibleSelection = false;

        Array.from(livestockSelect.options).forEach((option) => {
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
            livestockSelect.value = '';
        }
    }

    function updateAge() {
        if (! birthDateInput || ! ageDisplay) {
            return;
        }

        ageDisplay.value = formatAgeFromBirthDate(birthDateInput.value);
    }

    farmSelect?.addEventListener('change', filterLivestockOptions);
    birthDateInput?.addEventListener('change', updateAge);
    birthDateInput?.addEventListener('input', updateAge);

    photoInput?.addEventListener('change', () => {
        const file = photoInput.files?.[0];

        if (! file || ! photoPreview) {
            return;
        }

        photoPreview.src = URL.createObjectURL(file);
        photoPreview.hidden = false;
    });

    filterLivestockOptions();
    updateAge();
});
