@extends('layouts.milk-module')

@section('title', 'Milking session')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => $milkSession->session_code,
        'subtitle' => $milkSession->farm->name.' · '.$milkSession->livestock->name.' · '.$milkSession->shiftLabel(),
        'backRoute' => 'milk.sessions',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('session') || $errors->has('record') || $errors->has('complete') || $errors->has('bulk'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">
            {{ $errors->first('session') ?: $errors->first('record') ?: $errors->first('complete') ?: $errors->first('bulk') }}
        </div>
    @endif

    @if (session('bulk_warnings'))
        <div class="dash-panel" style="margin-bottom: 1rem; border-color: #fbbf24; background: #fffbeb;">
            <div class="dash-panel-title">Some rows were skipped</div>
            <ul style="font-size: 0.8125rem; color: #92400e; margin: 0; padding-left: 1.25rem;">
                @foreach (session('bulk_warnings') as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Total yield</div>
            <div class="dash-stat-value">{{ number_format($milkSession->total_yield_liters, 2) }} L</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Animals milked</div>
            <div class="dash-stat-value accent">{{ $milkSession->number_of_animals_milked }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Average / animal</div>
            <div class="dash-stat-value">{{ number_format($milkSession->average_yield_per_animal, 2) }} L</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Status</div>
            <div class="dash-stat-value">{{ ucfirst($milkSession->status) }}</div>
        </div>
    </div>

    @if ($milkSession->isOpen())
        <form method="POST" action="{{ route('milk.sessions.update', $milkSession) }}" class="dash-farm-form" style="margin-bottom: 1.25rem;">
            @csrf
            @method('PUT')
            @include('modules.milk.sessions._form', ['milkSession' => $milkSession])
            <div class="dash-form-section dash-form-section--actions">
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">Save session</button>
                </div>
            </div>
        </form>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Animal yields</div>
        <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem;">
            Record how much milk each animal gave in this session. Animals in <strong>{{ $milkSession->livestock->name }}</strong> qualify if they are in a lactating herd group (e.g. Cows (lactating)) or have production status <strong>Lactating</strong>.
        </p>
        @if ($milkSession->records->isEmpty())
            <p class="dash-empty">No animals recorded yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Animal</th>
                            <th>Yield (L)</th>
                            <th>Abnormal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($milkSession->records as $record)
                            <tr>
                                <td>
                                    <strong>{{ $record->animal->tag_number }}</strong>
                                    @if ($record->animal->name)
                                        <span style="color: #808080;">{{ $record->animal->name }}</span>
                                    @endif
                                </td>
                                <td>{{ number_format($record->yield_liters, 2) }}</td>
                                <td>{{ $record->abnormal_milk ? 'Yes' : '—' }}</td>
                                <td>
                                    @if ($milkSession->isOpen())
                                        <form method="POST" action="{{ route('milk.records.destroy', $record) }}" style="display:inline;" onsubmit="return confirm('Remove this record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dash-btn-link" style="color:#b45309;">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if ($milkSession->isOpen())
        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Bulk entry</div>
            <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem;">
                Fill yields for multiple animals at once, or paste lines like <code>TAG001, 12.5</code> (one per line).
            </p>

            <form method="POST" action="{{ route('milk.sessions.records.bulk', $milkSession) }}" class="dash-farm-form">
                @csrf

                @if ($sessionAnimals->isNotEmpty())
                    <div class="dash-table-wrap" style="margin-bottom: 1rem;">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Tag</th>
                                    <th>Name</th>
                                    <th style="width: 140px;">Yield (L)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sessionAnimals as $animal)
                                    <tr>
                                        <td><strong>{{ $animal->tag_number }}</strong></td>
                                        <td>{{ $animal->name ?: '—' }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                name="yields[{{ $animal->id }}]"
                                                value="{{ old('yields.'.$animal->id) }}"
                                                placeholder="0.00"
                                                style="width: 100%; padding: 0.35rem 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem;"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @if (($herdLactatingCount ?? 0) === 0)
                        <p class="dash-empty" style="margin-bottom: 1rem;">
                            No milking-eligible animals in this herd. Assign animals to <strong>{{ $milkSession->livestock->name }}</strong>, or set production status to Lactating on individual animals.
                            <a href="{{ route('animals.index') }}">Edit animals</a>
                        </p>
                    @else
                        <p class="dash-empty" style="margin-bottom: 1rem;">Every lactating animal in this herd is already recorded above. Use paste only if you need to retry a tag.</p>
                    @endif
                @endif

                <div class="dash-form-field dash-form-field--full">
                    <label for="bulk_lines">Or paste tag + liters</label>
                    <textarea
                        name="bulk_lines"
                        id="bulk_lines"
                        rows="5"
                        placeholder="# One animal per line&#10;COW-001, 14.5&#10;COW-002, 13.2&#10;COW-003 11.0"
                        style="font-family: ui-monospace, monospace; font-size: 0.8125rem;"
                    >{{ old('bulk_lines') }}</textarea>
                </div>

                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">Save all yields</button>
                </div>
            </form>
        </div>

        <div class="dash-panel" style="margin-bottom: 1.25rem;">
            <div class="dash-panel-title">Add single animal</div>
            @if ($sessionAnimals->isEmpty())
                @if (($herdLactatingCount ?? 0) === 0)
                    <p class="dash-empty">No milking-eligible animals left in this herd — check that animals belong to <strong>{{ $milkSession->livestock->name }}</strong> and the session uses the same herd.</p>
                @else
                    <p class="dash-empty">All lactating animals in this herd are already recorded in this session.</p>
                @endif
            @else
                <form method="POST" action="{{ route('milk.sessions.records.store', $milkSession) }}" class="dash-farm-form">
                    @csrf
                    <div class="dash-form-grid">
                        <div class="dash-form-field">
                            <label for="animal_id">Animal <span class="dash-required">*</span></label>
                            <select name="animal_id" id="animal_id" required data-selected="{{ old('animal_id') }}">
                                <option value="">Select animal</option>
                                @foreach ($sessionAnimals as $animal)
                                    <option value="{{ $animal->id }}" @selected(old('animal_id') == $animal->id)>
                                        {{ $animal->tag_number }} — {{ $animal->name ?: 'Unnamed' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dash-form-field">
                            <label for="yield_liters">Yield (liters) <span class="dash-required">*</span></label>
                            <input type="number" step="0.01" min="0.01" name="yield_liters" id="yield_liters" value="{{ old('yield_liters') }}" required>
                        </div>
                        <div class="dash-form-field">
                            <label for="udder_condition">Udder condition</label>
                            <select name="udder_condition" id="udder_condition">
                                <option value="">Normal</option>
                                @foreach (config('modules.udder_conditions') as $condition)
                                    <option value="{{ $condition }}" @selected(old('udder_condition') === $condition)>{{ ucfirst(str_replace('_', ' ', $condition)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="dash-form-field">
                            <label>
                                <input type="checkbox" name="abnormal_milk" value="1" @checked(old('abnormal_milk'))> Abnormal milk
                            </label>
                        </div>
                        <div class="dash-form-field dash-form-field--full">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="dash-form-actions">
                        <button type="submit" class="dash-btn-save">Add yield</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Complete session</div>
            <p style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 1rem;">Mark milking done and optionally transfer total yield into storage.</p>
            <form method="POST" action="{{ route('milk.sessions.complete', $milkSession) }}" class="dash-form-grid" style="align-items: end;">
                @csrf
                <div class="dash-form-field">
                    <label for="complete_storage_id">Destination tank</label>
                    <select name="destination_storage_id" id="complete_storage_id">
                        <option value="">Skip storage (totals only)</option>
                        @foreach ($storageTanks as $tank)
                            <option value="{{ $tank->id }}" @selected(old('destination_storage_id', $milkSession->destination_storage_id) == $tank->id)>
                                {{ $tank->container_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">Complete session</button>
                </div>
            </form>
            <form method="POST" action="{{ route('milk.sessions.cancel', $milkSession) }}" style="margin-top: 0.75rem;" onsubmit="return confirm('Cancel this session?');">
                @csrf
                <button type="submit" class="dash-btn-cancel">Cancel session</button>
            </form>
        </div>
    @endif

    @if ($milkSession->isOpen())
        <script>
            (() => {
                const herdSelect = document.getElementById('livestock_id');
                const animalSelect = document.getElementById('animal_id');

                if (!herdSelect || !animalSelect) {
                    return;
                }

                const animalsByLivestock = @json($eligibleAnimalsByLivestock ?? []);
                let preferredAnimal = String(animalSelect.dataset.selected || '');

                const refreshAnimals = () => {
                    const livestockId = String(herdSelect.value || '');
                    const animals = livestockId ? (animalsByLivestock[livestockId] || []) : [];

                    animalSelect.innerHTML = '';
                    animalSelect.append(new Option('Select animal', ''));

                    for (const animal of animals) {
                        const label = `${animal.tag_number} — ${animal.name || 'Unnamed'}`;
                        animalSelect.append(new Option(label, String(animal.id)));
                    }

                    if (preferredAnimal && animals.some((a) => String(a.id) === preferredAnimal)) {
                        animalSelect.value = preferredAnimal;
                    } else {
                        animalSelect.value = '';
                    }

                    preferredAnimal = '';
                };

                herdSelect.addEventListener('change', () => {
                    preferredAnimal = '';
                    refreshAnimals();
                });

                refreshAnimals();
            })();
        </script>
    @endif
@endsection
