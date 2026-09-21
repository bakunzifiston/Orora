@extends('layouts.eggs-module')

@section('title', __('Eggs — Overview'))

@section('eggs-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Eggs'),
            'createRoute' => 'eggs.collections.create',
            'createLabel' => '+ '. __('Record collection'),
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('eggs.overview') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="farm_id">{{ __('Farm') }}</label>
                    <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                        <option value="">{{ __('All poultry farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected((string) $farmId === (string) $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('eggs.collections') }}" class="farm-kpi farm-kpi--eggs">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Eggs (30 days)') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['eggs']) }}</div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Saleable') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['saleable']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Lay rate') }}</div>
                        <div class="farm-kpi__value">
                            {{ $stats['lay_rate'] }}
                            <span class="farm-kpi__unit">%</span>
                        </div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Birds on hand') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['birds']) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Recent collections') }}</h2>
                    @if ($recent->isNotEmpty())
                        <p class="farm-panel__desc">{{ $recent->count() }} {{ __('records') }}</p>
                    @endif
                </div>
            </header>
            @if ($recent->isEmpty())
                <div class="dash-panel dash-entity-empty" style="box-shadow: none; border: 0;">
                    <div class="dash-entity-empty__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <p class="dash-empty">{{ __('No egg collections yet.') }}</p>
                    <a href="{{ route('eggs.collections.create') }}" class="dash-btn-save">{{ __('Record collection') }}</a>
                </div>
            @else
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Collection') }}</th>
                                <th>{{ __('Flock') }}</th>
                                <th>{{ __('Eggs') }}</th>
                                <th>{{ __('Cracked') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $row)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'box', 'tone' => $row->cracked_count > 0 ? 'warn' : 'ok'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $row->collected_on->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $row->flock?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($row->eggs_count) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($row->cracked_count) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
