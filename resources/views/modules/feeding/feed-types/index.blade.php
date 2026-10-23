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
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Unit') }}</th>
                                <th>{{ __('Stock (all farms)') }}</th>
                                <th>{{ __('Status') }}</th>
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
                                                @if ($feedType->description)
                                                    <span class="health-table__meta">{{ \Illuminate\Support\Str::limit($feedType->description, 60) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $feedType->category ? ucfirst($feedType->category) : '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $feedType->unit }}</span>
                                    </td>
                                    <td>
                                        @if ($feedType->inventories_count > 0)
                                            <div class="health-table__stack">
                                                <span class="health-table__value">{{ rtrim(rtrim(number_format((float) $feedType->inventories_sum_quantity_on_hand, 2), '0'), '.') }} {{ $feedType->unit }}</span>
                                                <span class="health-table__meta">{{ trans_choice('{1} On 1 farm|[2,*] On :count farms', $feedType->inventories_count, ['count' => $feedType->inventories_count]) }}</span>
                                            </div>
                                        @else
                                            <span class="health-table__meta">{{ __('Not stocked yet') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $feedType->is_active ? 'ok' : 'muted' }}">{{ $feedType->is_active ? __('Active') : __('Inactive') }}</span>
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
