@extends('layouts.breeding-module')

@section('title', __('Breeding — Pregnancy checks'))

@section('breeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Pregnancy checks'),
            'createRoute' => 'breeding.checks.create',
            'createLabel' => '+ '. __('Add check'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('breeding.checks') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="result">{{ __('Result') }}</label>
                    <select name="result" id="result" onchange="this.form.submit()">
                        <option value="">{{ __('All results') }}</option>
                        @foreach (config('modules.pregnancy_check_results') as $result)
                            <option value="{{ $result }}" @selected(request('result') === $result)>{{ config('modules.pregnancy_check_result_labels')[$result] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        @if ($checks->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                </div>
                <p class="dash-empty">{{ __('No pregnancy checks yet.') }}</p>
                <a href="{{ route('breeding.checks.create') }}" class="dash-btn-save">{{ __('Add check') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Check') }}</th>
                                <th>{{ __('Female') }}</th>
                                <th>{{ __('Breeding') }}</th>
                                <th>{{ __('Method') }}</th>
                                <th>{{ __('Result') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($checks as $check)
                                @php
                                    $resultTone = match ($check->result) {
                                        'confirmed_pregnant' => 'ok',
                                        'not_pregnant' => 'bad',
                                        'inconclusive' => 'warn',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'health', 'tone' => $resultTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $check->check_code }}</span>
                                                <span class="health-table__meta">{{ $check->check_date->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $check->animal->tag_number }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('breeding.records.edit', $check->breedingRecord) }}" class="health-table__title health-table__title--link">{{ $check->breedingRecord->breeding_code }}</a>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $check->methodLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $resultTone }}">{{ $check->resultLabel() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $checks->links() }}</div>
        @endif
    </div>
@endsection
