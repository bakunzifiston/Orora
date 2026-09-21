@extends('layouts.feeding-module')

@section('title', __('Feeding — Overview'))

@section('feeding-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Feeding'),
            'createRoute' => 'feeding.records.create',
            'createLabel' => '+ '. __('Log feeding'),
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <a href="{{ route('feeding.suppliers') }}" class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Suppliers') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['suppliers']) }}</div>
                    </div>
                </a>
                <a href="{{ route('feeding.feed-types') }}" class="farm-kpi farm-kpi--feeding">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'feeding'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Feed types') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['feed_types']) }}</div>
                    </div>
                </a>
                <a href="{{ route('feeding.inventory') }}" class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Inventory items') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['inventory_items']) }}</div>
                    </div>
                </a>
                <a href="{{ route('feeding.inventory') }}" class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Low stock') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['low_stock']) }}</div>
                    </div>
                </a>
                <a href="{{ route('feeding.schedules') }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active schedules') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active_schedules']) }}</div>
                    </div>
                </a>
                <a href="{{ route('feeding.records') }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Records this month') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['records_this_month']) }}</div>
                    </div>
                </a>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Recent lists') }}">
            <div class="farm-dash__charts farm-dash__charts--2">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Recent feeding records') }}</h2>
                            @if ($recentFeedings->isNotEmpty())
                                <p class="farm-panel__desc">{{ $recentFeedings->count() }} {{ __('records') }}</p>
                            @endif
                        </div>
                    </header>
                    @if ($recentFeedings->isEmpty())
                        <p class="dash-empty">{{ __('No feeding records yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($recentFeedings as $feeding)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <span class="farm-activity__title">{{ $feeding->feedType?->name }}</span>
                                        <span class="farm-activity__meta">{{ $feeding->farm->name }} · {{ $feeding->fed_on->format('M j, Y') }}</span>
                                    </div>
                                    <span class="health-table__pill health-table__pill--muted">{{ $feeding->quantity }} {{ $feeding->unit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Low stock alerts') }}</h2>
                            @if ($lowStockItems->isNotEmpty())
                                <p class="farm-panel__desc">{{ $lowStockItems->count() }} {{ __('items') }}</p>
                            @endif
                        </div>
                    </header>
                    @if ($lowStockItems->isEmpty())
                        <p class="dash-empty">{{ __('No low-stock items.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($lowStockItems as $item)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <span class="farm-activity__title">{{ $item->feedType->name }}</span>
                                        <span class="farm-activity__meta">{{ $item->farm->name }}</span>
                                    </div>
                                    <span class="health-table__pill health-table__pill--warn">{{ $item->quantity_on_hand }} {{ $item->unit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            </div>
        </section>
    </div>
@endsection
