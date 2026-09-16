@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    @if (empty($usersReady))
        <div class="dash-panel">
            <p class="dash-empty">User tables are not set up yet. Run <code>php artisan migrate --force</code>.</p>
        </div>
    @else
        <form method="GET" action="{{ route('central.accounts.index') }}" class="dash-ops-toolbar" id="admin-accounts-filters-form">
            <div class="dash-ops-toolbar__brand">
                <h1 class="dash-welcome" style="margin: 0;">Users</h1>
                <p class="dash-home-subtitle" style="margin: 0.25rem 0 0;">Everyone with a login on the platform.</p>
            </div>
            <div class="dash-ops-toolbar__controls">
                <div class="dash-ops-field">
                    <label for="admin_accounts_search">Search</label>
                    <input type="search" name="q" id="admin_accounts_search" value="{{ $search }}" placeholder="Name, email, or workspace" style="min-width: 16rem;">
                </div>
                <div class="dash-ops-field">
                    <label for="admin_accounts_status">Status</label>
                    <select name="status" id="admin_accounts_status">
                        <option value="all" @selected($status === 'all')>All</option>
                        <option value="with_farm" @selected($status === 'with_farm')>Has farm</option>
                        <option value="pending" @selected($status === 'pending')>No farm</option>
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">Apply</button>
                @if (! empty($filtersActive))
                    <a href="{{ route('central.accounts.index') }}" class="dash-back-link" style="align-self: flex-end; padding-bottom: 0.45rem;">Clear</a>
                @endif
            </div>
        </form>

        <section class="dash-ops-row" aria-label="Users summary" style="margin-bottom: 1.25rem;">
            <div class="dash-stats admin-kpis">
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Users</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['total']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Users'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">With farm</div>
                        <div class="dash-stat-value">{{ number_format($stats['with_farm']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Users with farm'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">No farm</div>
                        <div class="dash-stat-value">{{ number_format($stats['pending']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'customer', 'label' => 'Users without farm'])
                </div>
            </div>
        </section>

        <div class="dash-panel dash-panel--flush dash-data-table-panel">
            <div class="admin-panel-head" style="padding: 1.25rem 1.25rem 0;">
                <h2 class="dash-panel-title">All users</h2>
                <span class="admin-panel-meta">{{ number_format($users->total()) }}</span>
            </div>

            @if ($users->isEmpty())
                <p class="dash-data-table__empty">
                    @if (! empty($filtersActive))
                        No users match the selected filters.
                    @else
                        No users registered yet.
                    @endif
                </p>
            @else
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Workspace</th>
                                <th class="dash-data-table__num">Farms</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="dash-data-table__action">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $account)
                                @php
                                    $hasFarm = $account->farms_count > 0;
                                    $statusBadge = $hasFarm
                                        ? 'dash-data-table__badge--active'
                                        : 'dash-data-table__badge--pending';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="dash-data-table__primary">
                                            <div class="dash-data-table__title-row">
                                                <a href="{{ route('central.accounts.show', $account) }}" class="dash-data-table__link">{{ $account->name }}</a>
                                            </div>
                                            <span class="dash-data-table__meta">{{ $account->email }}</span>
                                        </div>
                                    </td>
                                    <td class="dash-data-table__muted">{{ $account->tenant?->name ?: ($account->tenant_id ?: '—') }}</td>
                                    <td class="dash-data-table__num">{{ number_format($account->farms_count) }}</td>
                                    <td>
                                        <span class="dash-data-table__badge {{ $statusBadge }}">
                                            {{ $hasFarm ? 'Has farm' : 'No farm' }}
                                        </span>
                                    </td>
                                    <td class="dash-data-table__muted">{{ $account->created_at?->format('M j, Y') ?? '—' }}</td>
                                    <td class="dash-data-table__action">
                                        <div class="dash-data-table__actions">
                                            <a href="{{ route('central.accounts.show', $account) }}" class="dash-data-table__view">View</a>
                                            @include('central.accounts.partials.delete-form', [
                                                'user' => $account,
                                                'confirm' => 'Delete '.$account->name.' and all related records (farms, animals, milk, sales, and the rest of this workspace)? This cannot be undone.',
                                            ])
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dash-pagination">{{ $users->links() }}</div>
            @endif
        </div>
    @endif
@endsection
