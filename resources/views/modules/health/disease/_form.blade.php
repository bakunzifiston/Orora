@php
    $diseaseRecord = $diseaseRecord ?? null;
    $selectedFarmId = old('farm_id', $selectedFarmId ?? $diseaseRecord?->farm_id);
    $selectedLivestockId = old('livestock_id', $selectedLivestockId ?? $diseaseRecord?->livestock_id);
    $selectedAnimalId = old('animal_id', $selectedAnimalId ?? $diseaseRecord?->animal_id);
@endphp

<div class="disease-form">
    @component('modules.farms._form-section', [
        'number' => '1',
        'title' => 'Farm, herd, and animal',
        'description' => 'Select the affected animal using the farm and livestock cascade.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field">
                <label for="farm_id">Farm <span class="dash-required">*</span></label>
                <select name="farm_id" id="farm_id" required>
                    <option value="">Select farm</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected((string) $selectedFarmId === (string) $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="livestock_id">Livestock <span class="dash-required">*</span></label>
                <select name="livestock_id" id="livestock_id" required data-selected="{{ $selectedLivestockId }}">
                    <option value="">Select livestock</option>
                    @foreach ($livestockGroups as $group)
                        <option value="{{ $group->id }}" @selected((string) $selectedLivestockId === (string) $group->id)>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="animal_id">Animal <span class="dash-required">*</span></label>
                <select name="animal_id" id="animal_id" required data-selected="{{ $selectedAnimalId }}">
                    <option value="">Select animal</option>
                </select>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '2',
        'title' => 'Disease details',
        'description' => 'Diagnosis information and clinical classification.',
    ])
        <div class="dash-form-grid">
            @if ($diseaseRecord?->disease_code)
                <div class="dash-form-field">
                    <label>Record code</label>
                    <input type="text" value="{{ $diseaseRecord->disease_code }}" readonly>
                </div>
            @endif
            <div class="dash-form-field @if (! $diseaseRecord?->disease_code) dash-form-field--full @endif">
                <label for="disease_name">Disease name <span class="dash-required">*</span></label>
                <input type="text" name="disease_name" id="disease_name" value="{{ old('disease_name', $diseaseRecord?->disease_name) }}" required placeholder="e.g. East Coast Fever">
            </div>
            <div class="dash-form-field">
                <label for="diagnosis_date">Diagnosis date <span class="dash-required">*</span></label>
                <input type="date" name="diagnosis_date" id="diagnosis_date" max="{{ now()->format('Y-m-d') }}" value="{{ old('diagnosis_date', $diseaseRecord?->diagnosis_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Severity <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_severity_levels') as $level)
                        <label class="dash-checkbox">
                            <input type="radio" name="severity_level" value="{{ $level }}" @checked(old('severity_level', $diseaseRecord?->severity_level ?? 'medium') === $level) required>
                            <span>{{ config('modules.disease_severity_labels')[$level] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Recovery status <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_recovery_statuses') as $status)
                        <label class="dash-checkbox">
                            <input type="radio" name="recovery_status" value="{{ $status }}" @checked(old('recovery_status', $diseaseRecord?->recovery_status ?? 'recovering') === $status) required>
                            <span>{{ config('modules.disease_recovery_labels')[$status] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label>Contagious status <span class="dash-required">*</span></label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    @foreach (config('modules.disease_contagious_statuses') as $status)
                        <label class="dash-checkbox">
                            <input type="radio" name="contagious_status" value="{{ $status }}" @checked(old('contagious_status', $diseaseRecord?->contagious_status ?? 'unknown') === $status) required>
                            <span>{{ config('modules.disease_contagious_labels')[$status] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label class="dash-checkbox">
                    <input type="checkbox" name="quarantine_required" value="1" @checked(old('quarantine_required', $diseaseRecord?->quarantine_required))>
                    <span>Yes, this animal needs quarantine</span>
                </label>
            </div>
        </div>
    @endcomponent

    @component('modules.farms._form-section', [
        'number' => '3',
        'title' => 'Clinical notes and files',
        'description' => 'Symptoms, veterinarian, notes, and supporting documents.',
    ])
        <div class="dash-form-grid">
            <div class="dash-form-field dash-form-field--full">
                <label for="symptoms">Symptoms</label>
                <textarea name="symptoms" id="symptoms" rows="3" placeholder="Observed symptoms…">{{ old('symptoms', $diseaseRecord?->symptoms) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="veterinarian_name">Veterinarian name</label>
                <input type="text" name="veterinarian_name" id="veterinarian_name" value="{{ old('veterinarian_name', $diseaseRecord?->veterinarian_name) }}">
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3">{{ old('notes', $diseaseRecord?->notes) }}</textarea>
            </div>
            <div class="dash-form-field dash-form-field--full">
                <label for="attachment">Attachment</label>
                <div class="dash-photo-field">
                    @if ($diseaseRecord?->attachmentUrl())
                        <p class="dash-field-hint">
                            Current file:
                            <a href="{{ $diseaseRecord->attachmentUrl() }}" target="_blank" rel="noopener">View attachment</a>
                        </p>
                    @endif
                    <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png">
                    <p class="dash-field-hint">PDF, JPG, or PNG up to 4 MB.</p>
                </div>
            </div>
        </div>
    @endcomponent

    @include('modules.expenses.partials.linked-expense-fields', [
        'expense' => $diseaseRecord?->expense,
        'defaultVendorName' => $diseaseRecord?->veterinarian_name,
        'vendors' => $vendors ?? [],
        'sectionNumber' => '4',
    ])
</div>

<script>
    (() => {
        const farmSelect = document.getElementById('farm_id');
        const livestockSelect = document.getElementById('livestock_id');
        const animalSelect = document.getElementById('animal_id');

        if (!farmSelect || !livestockSelect || !animalSelect) {
            return;
        }

        const livestockByFarm = @json($livestockByFarm ?? []);
        const animalsByLivestock = @json($animalsByLivestock ?? []);
        let preferredLivestock = String(livestockSelect.dataset.selected || '');
        let preferredAnimal = String(animalSelect.dataset.selected || '');

        const renderLivestockOptions = () => {
            const farmId = String(farmSelect.value || '');
            const herds = farmId ? (livestockByFarm[farmId] || []) : [];

            livestockSelect.innerHTML = '';
            livestockSelect.append(new Option('Select livestock', ''));

            for (const herd of herds) {
                livestockSelect.append(new Option(herd.name, String(herd.id)));
            }

            if (preferredLivestock && herds.some((h) => String(h.id) === preferredLivestock)) {
                livestockSelect.value = preferredLivestock;
            } else if (herds.length === 1) {
                livestockSelect.value = String(herds[0].id);
            } else {
                livestockSelect.value = '';
            }

            preferredLivestock = '';
            renderAnimalOptions();
        };

        const renderAnimalOptions = () => {
            const livestockId = String(livestockSelect.value || '');
            const animals = livestockId ? (animalsByLivestock[livestockId] || []) : [];

            animalSelect.innerHTML = '';
            animalSelect.append(new Option('Select animal', ''));

            for (const animal of animals) {
                animalSelect.append(new Option(animal.label, String(animal.id)));
            }

            if (preferredAnimal && animals.some((a) => String(a.id) === preferredAnimal)) {
                animalSelect.value = preferredAnimal;
            } else if (animals.length === 1) {
                animalSelect.value = String(animals[0].id);
            } else {
                animalSelect.value = '';
            }

            preferredAnimal = '';
        };

        farmSelect.addEventListener('change', () => {
            preferredLivestock = '';
            preferredAnimal = '';
            renderLivestockOptions();
        });

        livestockSelect.addEventListener('change', () => {
            preferredAnimal = '';
            renderAnimalOptions();
        });

        renderLivestockOptions();
    })();
</script>
