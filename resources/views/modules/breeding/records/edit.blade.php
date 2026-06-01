@extends('layouts.breeding-module')

@section('title', 'Breeding — '.$breedingRecord->breeding_code)

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => $breedingRecord->breeding_code,
        'subtitle' => $breedingRecord->femaleAnimal->tag_number.' · '.$breedingRecord->statusLabel(),
        'backRoute' => 'breeding.records',
    ])
    @include('modules.partials.flash')

    @if ($breedingRecord->breeding_status === 'pending' && $breedingRecord->pregnancyChecks->isEmpty() && ($pregnancyCheckDue || ($daysUntilPregnancyCheck !== null && $daysUntilPregnancyCheck <= 7)))
        @include('modules.breeding.partials.pregnancy-check-reminder', [
            'breedingRecord' => $breedingRecord,
            'pregnancyCheckDue' => $pregnancyCheckDue,
            'daysUntilPregnancyCheck' => $daysUntilPregnancyCheck,
        ])
    @endif

    @if ($errors->has('breeding'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">{{ $errors->first('breeding') }}</div>
    @endif

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div class="dash-stat-label">Breeding date</div>
            <div class="dash-stat-value">{{ $breedingRecord->breeding_date->format('M j, Y') }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Expected calving</div>
            <div class="dash-stat-value accent">{{ $breedingRecord->expected_calving_date?->format('M j, Y') ?? '—' }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Sire</div>
            <div class="dash-stat-value" style="font-size: 1rem;">{{ $breedingRecord->sireLabel() }}</div>
        </div>
        <div class="dash-stat-card">
            <div class="dash-stat-label">Status</div>
            <div class="dash-stat-value">{{ $breedingRecord->statusLabel() }}</div>
        </div>
    </div>

    @if (! $breedingRecord->birthRecord)
        <form method="POST" action="{{ route('breeding.records.update', $breedingRecord) }}" class="dash-farm-form" style="margin-bottom: 1.25rem;">
            @csrf
            @method('PUT')
            @include('modules.breeding.records._form', ['breedingRecord' => $breedingRecord])
            <div class="dash-form-section dash-form-section--actions">
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">Save changes</button>
                </div>
            </div>
        </form>
    @endif

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Pregnancy checks</div>
        @if ($breedingRecord->pregnancyChecks->isEmpty())
            <p class="dash-empty">No checks yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Result</th>
                            <th>Checked by</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($breedingRecord->pregnancyChecks as $check)
                            <tr>
                                <td>{{ $check->check_code }}</td>
                                <td>{{ $check->check_date->format('M j, Y') }}</td>
                                <td>{{ $check->methodLabel() }}</td>
                                <td>{{ $check->resultLabel() }}</td>
                                <td>{{ $check->checked_by }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        @if (! $breedingRecord->birthRecord)
            <p style="margin-top: 1rem;">
                <a href="{{ route('breeding.checks.create', ['breeding_record_id' => $breedingRecord->id]) }}" class="dash-btn-save">+ Add pregnancy check</a>
            </p>
        @endif
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Birth</div>
        @if ($breedingRecord->birthRecord)
            <p>
                <a href="{{ route('breeding.births.edit', $breedingRecord->birthRecord) }}">{{ $breedingRecord->birthRecord->birth_code }}</a>
                — {{ $breedingRecord->birthRecord->birth_date->format('M j, Y') }}
                ({{ $breedingRecord->birthRecord->alive_offspring }} alive)
            </p>
        @elseif (in_array($breedingRecord->breeding_status, ['pending', 'confirmed_pregnant']))
            <p class="dash-empty">No birth recorded.</p>
            <a href="{{ route('breeding.births.create', ['breeding_record_id' => $breedingRecord->id]) }}" class="dash-btn-save">Record birth</a>
        @else
            <p class="dash-empty">Birth not applicable for this status.</p>
        @endif
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Activity log</div>
        @if ($breedingRecord->logs->isEmpty())
            <p class="dash-empty">No activity yet.</p>
        @else
            <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem;">
                @foreach ($breedingRecord->logs as $log)
                    <li style="margin-bottom: 0.5rem;">
                        <strong>{{ $log->actionLabel() }}</strong>
                        — {{ $log->action_date->format('M j, Y g:i A') }}
                        @if ($log->notes)
                            <span style="color: #6b7280;">({{ $log->notes }})</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
