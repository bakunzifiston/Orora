@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    @php
        $hasFarm = $farms->isNotEmpty();
        $statusClass = $hasFarm ? 'dash-farm-card__badge--active' : 'dash-farm-card__badge--pending';
    @endphp

    <div class="admin-farm-page">
        <div class="admin-farm-page__top">
            <div>
                <a href="{{ route('central.accounts.index') }}" class="dash-back-link">← Users</a>
                <div class="admin-farm-page__title-row">
                    <h1 class="dash-welcome">{{ $user->name }}</h1>
                    <span class="dash-farm-card__badge {{ $statusClass }}">{{ $hasFarm ? 'Has farm' : 'No farm' }}</span>
                </div>
                <p class="admin-farm-page__meta">{{ $user->email }}</p>
            </div>
        </div>

        <section class="dash-ops-row" aria-label="User summary">
            <div class="dash-stats admin-kpis">
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Farms</div>
                        <div class="dash-stat-value accent">{{ number_format($farms->count()) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farms'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Animals</div>
                        <div class="dash-stat-value">{{ number_format($farms->sum('animals_count')) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Joined</div>
                        <div class="dash-stat-value">{{ $user->created_at?->format('M j, Y') ?? '—' }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Joined'])
                </div>
            </div>
        </section>

        <div class="dash-health-grid">
            <div class="dash-panel">
                <h2 class="dash-panel-title">Account</h2>
                <dl class="dash-farm-detail">
                    @include('modules.farms._detail-row', ['label' => 'Name', 'value' => $user->name])
                    @include('modules.farms._detail-row', ['label' => 'Email', 'value' => $user->email])
                    @include('modules.farms._detail-row', ['label' => 'Verified', 'value' => $user->email_verified_at?->format('M j, Y') ?: 'Not verified'])
                    @include('modules.farms._detail-row', ['label' => 'Joined', 'value' => $user->created_at?->format('M j, Y g:i A')])
                </dl>
            </div>

            <div class="dash-panel">
                <h2 class="dash-panel-title">Workspace</h2>
                <dl class="dash-farm-detail">
                    @include('modules.farms._detail-row', ['label' => 'Name', 'value' => $user->tenant?->name])
                    @include('modules.farms._detail-row', ['label' => 'ID', 'value' => $user->tenant_id])
                </dl>
            </div>
        </div>

        <div class="dash-panel dash-panel--flush">
            <div class="admin-panel-head" style="padding: 1.25rem 1.25rem 0;">
                <h2 class="dash-panel-title">Farms</h2>
                <span class="admin-panel-meta">{{ number_format($farms->count()) }}</span>
            </div>
            @if ($farms->isEmpty())
                <p class="dash-data-table__empty">This user has not registered a farm yet.</p>
            @else
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>Farm</th>
                                <th>Location</th>
                                <th class="dash-data-table__num">Groups</th>
                                <th class="dash-data-table__num">Animals</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($farms as $farm)
                                @php
                                    $farmBadge = match ($farm->status) {
                                        'active' => 'dash-data-table__badge--active',
                                        'pending' => 'dash-data-table__badge--pending',
                                        default => 'dash-data-table__badge--inactive',
                                    };
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('central.farms.show', $farm) }}" class="dash-data-table__link">{{ $farm->name }}</a>
                                    </td>
                                    <td class="dash-data-table__muted">{{ Str::limit($farm->location_label ?: '—', 42) }}</td>
                                    <td class="dash-data-table__num">{{ number_format($farm->livestock_count) }}</td>
                                    <td class="dash-data-table__num">{{ number_format($farm->animals_count) }}</td>
                                    <td>
                                        @if ($farm->status)
                                            <span class="dash-data-table__badge {{ $farmBadge }}">{{ $farm->status }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
