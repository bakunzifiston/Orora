@extends('layouts.admin')

@section('title', __('Users'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-users">
        @if (empty($usersReady))
            <article class="farm-panel">
                <p class="dash-empty">{{ __('User tables are not set up yet. Run php artisan migrate --force.') }}</p>
            </article>
        @else
            <form method="GET" action="{{ route('central.accounts.index') }}" class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar" id="admin-accounts-filters-form">
                <div class="admin-dash__header-text dash-ops-toolbar__brand">
                    <h1 class="admin-dash__title">{{ __('Users') }}</h1>
                    <p class="admin-dash__period">{{ __('Everyone with a login on the platform.') }}</p>
                </div>
                <div class="dash-ops-toolbar__controls farms-page__filters">
                    <div class="dash-ops-field farms-page__search">
                        <label for="admin_accounts_search">{{ __('Search') }}</label>
                        <input type="search" name="q" id="admin_accounts_search" value="{{ $search }}" placeholder="{{ __('Name, email, or workspace') }}" autocomplete="off">
                    </div>
                    <div class="dash-ops-field">
                        <label for="admin_accounts_status">{{ __('Status') }}</label>
                        <select name="status" id="admin_accounts_status" onchange="this.form.submit()">
                            <option value="all" @selected($status === 'all')>{{ __('All') }}</option>
                            <option value="with_farm" @selected($status === 'with_farm')>{{ __('Has farm') }}</option>
                            <option value="pending" @selected($status === 'pending')>{{ __('No farm') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                    @if (! empty($filtersActive))
                        <a href="{{ route('central.accounts.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>

            <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
                <div class="farm-dash__kpis farm-dash__kpis--4">
                    <div class="farm-kpi farm-kpi--receivable">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Users') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--farms">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('With farm') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['with_farm']) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--stock">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('No farm') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['pending']) }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="farm-dash__section" aria-label="{{ __('All users') }}">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('All users') }}</h2>
                            <p class="farm-panel__desc">{{ number_format($users->total()) }} {{ __('total') }}</p>
                        </div>
                    </header>

                    @if ($users->isEmpty())
                        <p class="dash-empty">
                            @if (! empty($filtersActive))
                                {{ __('No users match the selected filters.') }}
                            @else
                                {{ __('No users registered yet.') }}
                            @endif
                        </p>
                    @else
                        <div class="dash-table-wrap">
                            <table class="health-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Workspace') }}</th>
                                        <th>{{ __('Farms') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Joined') }}</th>
                                        <th>{{ __('Last sign in') }}</th>
                                        <th class="health-table__actions">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $account)
                                        @php
                                            $hasFarm = $account->farms_count > 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="health-table__primary">
                                                    @include('modules.health.partials.table-icon', ['icon' => 'customer', 'tone' => $hasFarm ? 'ok' : 'warn'])
                                                    <div class="health-table__stack">
                                                        <a href="{{ route('central.accounts.show', $account) }}" class="health-table__title health-table__title--link">{{ $account->name }}</a>
                                                        <span class="health-table__meta">{{ $account->email }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ $account->tenant?->name ?: ($account->tenant_id ?: '—') }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ number_format($account->farms_count) }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__pill health-table__pill--{{ $hasFarm ? 'ok' : 'warn' }}">
                                                    {{ $hasFarm ? __('Has farm') : __('No farm') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">{{ $account->created_at?->format('M j, Y') ?? '—' }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">
                                                    {{ $account->last_login_at?->format('M j, Y g:i A') ?? __('Never') }}
                                                </span>
                                            </td>
                                            <td class="health-table__actions">
                                                <div class="health-table__action-btns">
                                                    <a href="{{ route('central.accounts.show', $account) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                                    @include('central.accounts.partials.delete-form', [
                                                        'user' => $account,
                                                        'buttonClass' => 'dash-farm-card__btn dash-farm-card__btn--danger',
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
                </article>
            </section>
        @endif
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-users .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-users .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
        .admin-users .health-table__action-btns .dash-btn-save--sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
        }
    </style>
@endpush
