@extends('layouts.admin')

@section('title', 'Marketplace')

@section('content')
    <div class="admin-marketplace-page">
        <div class="admin-panel-head">
            <h1 class="dash-welcome" style="margin: 0;">Marketplace</h1>
            <span class="admin-panel-meta">{{ number_format($listings->total()) }} listings</span>
        </div>

        <section class="dash-ops-row" aria-label="Marketplace summary">
            <div class="dash-stats admin-kpis">
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Categories</div>
                        <div class="dash-stat-value">{{ number_format($categories->count()) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'box', 'label' => 'Categories'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Listings</div>
                        <div class="dash-stat-value accent">{{ number_format($listings->total()) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Listings'])
                </div>
            </div>
        </section>

        <div class="dash-panel dash-panel--flush">
            <div class="admin-panel-head" style="padding: 1.25rem 1.25rem 0;">
                <h2 class="dash-panel-title">Listings</h2>
            </div>

            @if (empty($shopReady))
                <p class="dash-data-table__empty">Marketplace is not set up yet.</p>
            @elseif ($listings->isEmpty())
                <p class="dash-data-table__empty">No listings yet.</p>
            @else
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Tenant</th>
                                <th class="dash-data-table__num">Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listings as $listing)
                                @php
                                    $statusBadge = match ($listing->status) {
                                        'active', 'published' => 'dash-data-table__badge--active',
                                        'pending' => 'dash-data-table__badge--pending',
                                        'sold', 'archived' => 'dash-data-table__badge--inactive',
                                        default => 'dash-data-table__badge--inactive',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <div class="dash-data-table__primary">
                                            <span class="dash-data-table__text">{{ $listing->title }}</span>
                                        </div>
                                    </td>
                                    <td class="dash-data-table__muted">{{ $listing->category?->name ?? '—' }}</td>
                                    <td class="dash-data-table__muted">{{ $listing->tenant?->name ?: $listing->tenant_id }}</td>
                                    <td class="dash-data-table__num">{{ number_format((float) $listing->price) }} {{ $listing->currency }}</td>
                                    <td><span class="dash-data-table__badge {{ $statusBadge }}">{{ $listing->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dash-pagination">{{ $listings->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .admin-marketplace-page {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .admin-marketplace-page > .admin-panel-head {
            margin-bottom: 0;
        }
        .admin-marketplace-page .dash-panel--flush .admin-panel-head .dash-panel-title {
            margin-bottom: 0;
        }
    </style>
@endpush
