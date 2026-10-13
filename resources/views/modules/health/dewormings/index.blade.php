@extends('layouts.health-module')

@section('title', __('Health — Deworming'))

@section('health-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Deworming'),
            'createRoute' => 'health.dewormings.create',
            'createLabel' => '+ '.__('Add deworming'),
        ])
        @include('modules.partials.flash')

        @if ($dewormings->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                </div>
                <p class="dash-empty">{{ __('No deworming records yet.') }}</p>
                <a href="{{ route('health.dewormings.create') }}" class="dash-btn-save">{{ __('Add deworming') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Dewormed') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Next due') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dewormings as $deworming)
                                @php
                                    $statusTone = match (strtolower((string) $deworming->status)) {
                                        'completed', 'done', 'administered' => 'ok',
                                        'scheduled', 'pending', 'due' => 'warn',
                                        'missed', 'cancelled' => 'bad',
                                        default => 'muted',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'health', 'tone' => $statusTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $deworming->product_name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $deworming->deworming_date->format('M j, Y') }}
                                                    @if ($deworming->product_type)
                                                        <span aria-hidden="true">·</span> {{ $deworming->product_type }}
                                                    @endif
                                                    @if ($deworming->batch_number)
                                                        <span aria-hidden="true">·</span> {{ $deworming->batch_number }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $deworming->stockLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $deworming->status }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $deworming->next_due_date?->format('M j, Y') ?? '—' }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $deworming,
                                            'editRoute' => 'health.dewormings.edit',
                                            'destroyRoute' => 'health.dewormings.destroy',
                                            'deleteConfirm' => __('Delete this deworming?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $dewormings->links() }}</div>
        @endif
    </div>
@endsection
