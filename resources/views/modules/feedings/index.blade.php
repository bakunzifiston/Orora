@extends('layouts.feeding-module')

@section('title', __('Feeding — Records'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Feeding records'),
            'createRoute' => 'feeding.records.create',
            'createLabel' => '+ '.__('Log feeding'),
        ])
        @include('modules.partials.flash')

        @if ($feedings->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'feeding'])
                </div>
                <p class="dash-empty">{{ __('No feeding records yet.') }}</p>
                <a href="{{ route('feeding.records.create') }}" class="dash-btn-save">{{ __('Log feeding') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Feed') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Target') }}</th>
                                <th>{{ __('Schedule') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($feedings as $feeding)
                                @php
                                    $targetLabel = $feeding->animal
                                        ? $feeding->animal->tag_number
                                        : ($feeding->livestock?->name ?? null);
                                    $fromSchedule = (bool) $feeding->feedingSchedule;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'feeding', 'tone' => $fromSchedule ? 'ok' : 'default'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $feeding->feedType?->name ?? '—' }}</span>
                                                <span class="health-table__meta">
                                                    {{ $feeding->fed_on->format('M j, Y') }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $feeding->quantity }} {{ $feeding->unit }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $feeding->farm->name }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $targetLabel ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $fromSchedule ? 'ok' : 'muted' }}">
                                            {{ $fromSchedule ? __('Scheduled') : __('Ad hoc') }}
                                        </span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $feeding,
                                            'editRoute' => 'feeding.records.edit',
                                            'destroyRoute' => 'feeding.records.destroy',
                                            'deleteConfirm' => __('Delete this feeding record?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $feedings->links() }}</div>
        @endif
    </div>
@endsection
