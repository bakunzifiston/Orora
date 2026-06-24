@extends('layouts.admin')

@section('title', 'Marketplace')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Marketplace',
        'subtitle' => 'Shop listings across all tenant workspaces.',
    ])

    <section class="dash-ops-row" aria-label="Marketplace summary">
        <div class="dash-stats" style="margin-bottom: 0;">
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

    <div class="dash-panel">
        <h2 class="dash-panel-title">Shop listings</h2>
        @if (empty($shopReady))
            <p class="dash-empty">Marketplace tables are not set up yet. Run <code>php artisan migrate --force</code> then <code>php artisan db:seed --class=MarketplaceSeeder --force</code>.</p>
        @elseif ($listings->isEmpty())
            <p class="dash-empty">No marketplace listings yet. Run <code>php artisan db:seed --class=MarketplaceSeeder --force</code> after migrations.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Tenant</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($listings as $listing)
                            <tr>
                                <td><strong>{{ $listing->title }}</strong></td>
                                <td>{{ $listing->category?->name ?? '—' }}</td>
                                <td><code>{{ $listing->tenant_id }}</code></td>
                                <td>{{ number_format((float) $listing->price) }} {{ $listing->currency }}</td>
                                <td><span class="dash-badge-green">{{ ucfirst($listing->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 1rem;">{{ $listings->links() }}</div>
        @endif
    </div>
@endsection
