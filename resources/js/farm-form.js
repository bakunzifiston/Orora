const apiBase = '/api/rwanda';

async function fetchOptions(url) {
    const response = await fetch(url, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!response.ok) {
        throw new Error('Failed to load location data');
    }

    return response.json();
}

function fillSelect(select, items, placeholder, selectedValue) {
    select.innerHTML = `<option value="">${placeholder}</option>`;

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = item.code;
        option.textContent = item.name;
        if (String(selectedValue) === String(item.code)) {
            option.selected = true;
        }
        select.appendChild(option);
    });

    select.disabled = items.length === 0;
}

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    select.disabled = true;
    select.value = '';
}

function initLocationCascade(root) {
    const province = root.querySelector('[data-location-province]');
    const district = root.querySelector('[data-location-district]');
    const sector = root.querySelector('[data-location-sector]');
    const cell = root.querySelector('[data-location-cell]');
    const village = root.querySelector('[data-location-village]');

    const initial = {
        province: root.dataset.initialProvince || '',
        district: root.dataset.initialDistrict || '',
        sector: root.dataset.initialSector || '',
        cell: root.dataset.initialCell || '',
        village: root.dataset.initialVillage || '',
    };

    async function loadDistricts(provinceCode, selected) {
        resetSelect(sector, 'Select sector');
        resetSelect(cell, 'Select cell');
        resetSelect(village, 'Select village');

        if (!provinceCode) {
            resetSelect(district, 'Select district');
            return;
        }

        const items = await fetchOptions(`${apiBase}/districts?province_code=${provinceCode}`);
        fillSelect(district, items, 'Select district', selected);
    }

    async function loadSectors(districtCode, selected) {
        resetSelect(cell, 'Select cell');
        resetSelect(village, 'Select village');

        if (!districtCode) {
            resetSelect(sector, 'Select sector');
            return;
        }

        const items = await fetchOptions(`${apiBase}/sectors?district_code=${districtCode}`);
        fillSelect(sector, items, 'Select sector', selected);
    }

    async function loadCells(sectorCode, selected) {
        resetSelect(village, 'Select village');

        if (!sectorCode) {
            resetSelect(cell, 'Select cell');
            return;
        }

        const items = await fetchOptions(`${apiBase}/cells?sector_code=${encodeURIComponent(sectorCode)}`);
        fillSelect(cell, items, 'Select cell', selected);
    }

    async function loadVillages(cellCode, selected) {
        if (!cellCode) {
            resetSelect(village, 'Select village');
            return;
        }

        const items = await fetchOptions(`${apiBase}/villages?cell_code=${cellCode}`);
        fillSelect(village, items, 'Select village', selected);
    }

    province?.addEventListener('change', () => {
        loadDistricts(province.value, '');
    });

    district?.addEventListener('change', () => {
        loadSectors(district.value, '');
    });

    sector?.addEventListener('change', () => {
        loadCells(sector.value, '');
    });

    cell?.addEventListener('change', () => {
        loadVillages(cell.value, '');
    });

    if (initial.province) {
        loadDistricts(initial.province, initial.district)
            .then(() => initial.district && loadSectors(initial.district, initial.sector))
            .then(() => initial.sector && loadCells(initial.sector, initial.cell))
            .then(() => initial.cell && loadVillages(initial.cell, initial.village))
            .catch(console.error);
    }
}

function initOwnershipToggle(root) {
    const ownership = root.querySelector('[data-ownership-type]');
    const orgFields = root.querySelectorAll('[data-org-field]');
    const membersSection = root.querySelector('[data-members-section]');
    const orgInputs = root.querySelectorAll('#organization_name, #tax_id');

    function toggle() {
        const type = ownership?.value || 'sole_proprietor';
        const needsOrg = type === 'cooperative' || type === 'company';

        orgFields.forEach((el) => {
            el.hidden = !needsOrg;
        });

        membersSection.hidden = !needsOrg;

        orgInputs.forEach((input) => {
            input.required = needsOrg;
            if (!needsOrg) {
                input.value = input.id === 'organization_name' || input.id === 'tax_id' ? input.value : '';
            }
        });

        root.querySelectorAll('[data-member-required]').forEach((input) => {
            input.required = needsOrg;
        });
    }

    ownership?.addEventListener('change', toggle);
    toggle();
}

function initMembers(root) {
    const list = root.querySelector('[data-members-list]');
    const template = document.getElementById('farm-member-template');
    const addButton = root.querySelector('[data-add-member]');
    let memberIndex = list?.querySelectorAll('[data-member-row]').length || 0;

    function reindexRows() {
        list.querySelectorAll('[data-member-row]').forEach((row, index) => {
            row.querySelectorAll('input, select').forEach((input) => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/members\[\d+]/, `members[${index}]`));
                }
            });
        });
        memberIndex = list.querySelectorAll('[data-member-row]').length;
    }

    addButton?.addEventListener('click', () => {
        if (!template || !list) {
            return;
        }

        const html = template.innerHTML.replaceAll('__INDEX__', String(memberIndex));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        list.appendChild(wrapper.firstElementChild);
        memberIndex += 1;

        const ownership = root.querySelector('[data-ownership-type]');
        const needsOrg = ownership?.value === 'cooperative' || ownership?.value === 'company';
        wrapper.querySelectorAll('[data-member-required]').forEach((input) => {
            input.required = needsOrg;
        });
    });

    list?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-member]');
        if (!button) {
            return;
        }

        const rows = list.querySelectorAll('[data-member-row]');
        if (rows.length <= 1) {
            rows[0].querySelectorAll('input, select').forEach((input) => {
                input.value = '';
            });
            return;
        }

        button.closest('[data-member-row]')?.remove();
        reindexRows();
    });
}

document.querySelectorAll('[data-farm-form]').forEach((root) => {
    initLocationCascade(root);
    initOwnershipToggle(root);
    initMembers(root);
});
