@extends('layouts.breeding-module')

@section('title', __('Breeding — Records'))

@section('breeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Breeding records'),
            'createRoute' => 'breeding.records.create',
            'createLabel' => '+ '. __('Record breeding'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('breeding.records') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="farm_id">{{ __('Farm') }}</label>
                    <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected(request('farm_id') == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="status">{{ __('Status') }}</label>
                    <select name="status" id="status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.breeding_statuses') as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ config('modules.breeding_status_labels')[$status] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field" style="display: flex; align-items: flex-end;">
                    <label class="dash-checkbox">
                        <input type="checkbox" name="pregnancy_check_due" value="1" @checked(request()->boolean('pregnancy_check_due')) onchange="this.form.submit()">
                        {{ __('Pregnancy check due only') }}
                    </label>
                </div>
            </div>
        </form>

        @if ($records->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'breeding'])
                </div>
                <p class="dash-empty">{{ __('No breeding records yet.') }}</p>
                <a href="{{ route('breeding.records.create') }}" class="dash-btn-save">{{ __('Record breeding') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Breeding') }}</th>
                                <th>{{ __('Female') }}</th>
                                <th>{{ __('Sire') }}</th>
                                <th>{{ __('Expected') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                @php
                                    $statusTone = match ($record->breeding_status) {
                                        'confirmed_pregnant', 'calved' => 'ok',
                                        'pending' => 'warn',
                                        'failed', 'aborted' => 'bad',
                                        default => 'muted',
                                    };
                                    $checkDue = $record->breeding_status === 'pending'
                                        && $record->pregnancyChecks->isEmpty()
                                        && $record->pregnancy_check_due_on
                                        && $record->pregnancy_check_due_on->lte(now()->startOfDay());
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'breeding', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $record->breeding_code }}</span>
                                                <span class="health-table__meta">
                                                    {{ $record->breeding_date->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $record->breedingTypeLabel() }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->femaleAnimal->tag_number }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $record->sireLabel() }}</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $record->expected_calving_date?->format('M j, Y') ?? '—' }}</span>
                                            @if ($record->breeding_status === 'pending' && $record->pregnancyChecks->isEmpty())
                                                <span class="health-table__meta">
                                                    {{ __('Check') }}: {{ $record->pregnancy_check_due_on?->format('M j, Y') ?? '—' }}
                                                    @if ($checkDue)
                                                        <span class="health-table__pill health-table__pill--warn">{{ __('Due') }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $record->statusLabel() }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $record,
                                            'editRoute' => 'breeding.records.edit',
                                            'destroyRoute' => 'breeding.records.destroy',
                                            'deleteConfirm' => __('Delete this breeding record?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
