@extends('layouts.eggs-module')

@section('title', __('Egg collections'))

@section('eggs-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Collections'),
            'createRoute' => 'eggs.collections.create',
            'createLabel' => '+ '. __('Record collection'),
        ])
        @include('modules.partials.flash')

        @if ($collections->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                </div>
                <p class="dash-empty">{{ __('No collections recorded yet.') }}</p>
                <a href="{{ route('eggs.collections.create') }}" class="dash-btn-save">{{ __('Record collection') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Collection') }}</th>
                                <th>{{ __('Flock') }}</th>
                                <th>{{ __('Eggs') }}</th>
                                <th>{{ __('Cracked') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($collections as $collection)
                                @php
                                    $tone = $collection->cracked_count > 0 ? 'warn' : 'ok';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'box', 'tone' => $tone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $collection->collection_code }}</span>
                                                <span class="health-table__meta">{{ $collection->collected_on->format('M j, Y') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $collection->flock?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($collection->eggs_count) }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $tone }}">{{ number_format($collection->cracked_count) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $collection,
                                            'editRoute' => 'eggs.collections.edit',
                                            'destroyRoute' => 'eggs.collections.destroy',
                                            'deleteConfirm' => __('Delete this egg collection?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $collections->links() }}</div>
        @endif
    </div>
@endsection
