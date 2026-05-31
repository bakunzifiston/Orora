@extends('layouts.feeding-module')

@section('title', 'Feeding — Overview')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Feeding overview',
        'subtitle' => 'Supplier → feed type → inventory → feeding records, with schedules and stock movements.',
        'createRoute' => 'feeding.records.create',
        'createLabel' => '+ Log feeding',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Suppliers</div>
                <div class="dash-stat-value">{{ number_format($stats['suppliers']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'farm'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Feed types</div>
                <div class="dash-stat-value accent">{{ number_format($stats['feed_types']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'feeding'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Inventory items</div>
                <div class="dash-stat-value">{{ number_format($stats['inventory_items']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'box'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Low stock</div>
                <div class="dash-stat-value">{{ number_format($stats['low_stock']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'movement'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Active schedules</div>
                <div class="dash-stat-value">{{ number_format($stats['active_schedules']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'certificate'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Records this month</div>
                <div class="dash-stat-value">{{ number_format($stats['records_this_month']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Recent feeding records</div>
            @if ($recentFeedings->isEmpty())
                <p class="dash-empty">No feeding records yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($recentFeedings as $feeding)
                        <li>
                            <div>
                                <strong>{{ $feeding->feedType?->name }}</strong> — {{ $feeding->farm->name }}
                                <span style="color: #808080;">{{ $feeding->fed_on->format('M j, Y') }}</span>
                            </div>
                            <span>{{ $feeding->quantity }} {{ $feeding->unit }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Low stock alerts</div>
            @if ($lowStockItems->isEmpty())
                <p class="dash-empty">No low-stock items.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($lowStockItems as $item)
                        <li>
                            <div>
                                <strong>{{ $item->feedType->name }}</strong> — {{ $item->farm->name }}
                            </div>
                            <span>{{ $item->quantity_on_hand }} {{ $item->unit }} left</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
