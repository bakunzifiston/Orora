@extends('layouts.feeding-module')

@section('title', __('Feeding — Feed types'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Feed types'),
            'createRoute' => 'feeding.feed-types.create',
            'createLabel' => '+ '.__('Add feed type'),
        ])
        @include('modules.partials.flash')

        @if ($feedTypes->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'feeding'])
                </div>
                <p class="dash-empty">{{ __('No feed types yet.') }}</p>
                <a href="{{ route('feeding.feed-types.create') }}" class="dash-btn-save">{{ __('Add feed type') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Feed type') }}</th>
                                <th>{{ __('Supplier') }}</th>
                                <th>{{ __('Inventory') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($feedTypes as $feedType)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'feeding', 'tone' => 'default'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $feedType->name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $feedType->unit }}
                                                    @if ($feedType->category)
                                                        <span aria-hidden="true">·</span> {{ $feedType->category }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $feedType->supplier?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($feedType->inventories_count) }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $feedType,
                                            'editRoute' => 'feeding.feed-types.edit',
                                            'destroyRoute' => 'feeding.feed-types.destroy',
                                            'deleteConfirm' => __('Delete this feed type?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $feedTypes->links() }}</div>
        @endif
    </div>
@endsection
