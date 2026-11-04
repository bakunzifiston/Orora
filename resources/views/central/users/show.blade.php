@extends('layouts.admin')

@section('title', $farm->name)

@section('content')
    @php
        $isPoultry = ($farm->primary_species ?? null) === 'poultry';
        $statusTone = match ($farm->status) {
            'active' => 'ok',
            'pending' => 'warn',
            'suspended' => 'bad',
            default => 'muted',
        };
        $ownershipLabel = config('modules.ownership_types')[$farm->ownership_type] ?? ucfirst(str_replace('_', ' ', (string) $farm->ownership_type));
        $backQuery = http_build_query(request()->only(['period', 'from', 'to', 'farm_id', 'province_code', 'district_code']));
        $backUrl = route('central.farms.index').($backQuery ? '?'.$backQuery : '');
        $location = collect([$farm->district, $farm->province, $farm->country])->filter()->implode(', ');
        $period = $filters['period'] ?? 'all';
    @endphp

    @php
            $money = $overview['money'];
            $health = $overview['health'];
            $wsUsers = $overview['users'];
            $lastActivity = $overview['last_activity'];
            $lastSignIn = $wsUsers->pluck('last_login_at')->filter()->max();
            $lastSignIn = $lastSignIn ? \Illuminate\Support\Carbon::parse($lastSignIn) : null;
    @endphp

    <div class="farm-dash farms-page health-page admin-dash admin-farms">
        <form method="GET" action="{{ route('central.farms.show', $farm) }}" class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar" id="admin-farm-filters-form">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <a href="{{ $backUrl }}" class="dash-back-link">← {{ __('Farms') }}</a>
                <div class="admin-farms__title-row">
                    <h1 class="admin-dash__title">{{ $farm->name }}</h1>
                    <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ ucfirst($farm->status) }}</span>
                    <span class="health-table__pill health-table__pill--{{ $isPoultry ? 'warn' : 'ok' }}">
                        {{ $isPoultry ? __('Poultry') : __('Cattle') }}
                    </span>
                </div>
                <p class="admin-dash__period">
                    {{ $location ?: __('Rwanda') }}
                    @if ($farm->registration_number)
                        · {{ $farm->registration_number }}
                    @endif
                    · {{ __('Showing') }}
                    <strong>{{ $filters['label'] ?? __('All time') }}</strong>
                </p>
                <p class="admin-farms__activity-line">
                    <span>{{ __('Last sign in') }}: <strong>{{ $lastSignIn ? $lastSignIn->diffForHumans() : __('Never') }}</strong></span>
                    <span>{{ __('Last record entered') }}: <strong title="{{ $lastActivity?->format('M j, Y H:i') }}">{{ $lastActivity ? $lastActivity->diffForHumans() : __('None yet') }}</strong></span>
                    <span>{{ __('Workspace users') }}: <strong>{{ number_format($wsUsers->count()) }}</strong></span>
                </p>
            </div>

            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="admin_farm_filter_period">{{ __('Period') }}</label>
                    <select name="period" id="admin_farm_filter_period" onchange="this.form.submit()">
                        <option value="all" @selected($period === 'all' || $period === '')>{{ __('All time') }}</option>
                        <option value="daily" @selected($period === 'daily')>{{ __('Daily') }}</option>
                        <option value="monthly" @selected($period === 'monthly')>{{ __('Monthly') }}</option>
                        <option value="yearly" @selected($period === 'yearly')>{{ __('Yearly') }}</option>
                        <option value="custom" @selected($period === 'custom')>{{ __('Custom') }}</option>
                    </select>
                </div>
                <div class="dash-ops-field dash-ops-field--dates @if($period !== 'custom') dash-ops-field--muted @endif" id="admin-farm-custom-dates">
                    <label>{{ __('Range') }}</label>
                    <div class="dash-ops-dates">
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="{{ __('From date') }}">
                        <span class="dash-ops-dates__sep">→</span>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="{{ __('To date') }}">
                    </div>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <h2 class="admin-farms__section-title">{{ __('Production and sales') }}</h2>
            <div class="farm-dash__kpis farm-dash__kpis--3">

                @if ($isPoultry)
                    <div class="farm-kpi farm-kpi--stock">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Flocks') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['flocks'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--production">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Birds on hand') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['birds'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--eggs">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Eggs collected') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['eggs_collected'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--revenue">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Eggs sold') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['eggs_sold'] ?? 0, 0) }}</div>
                        </div>
                    </div>
                @else
                    <div class="farm-kpi farm-kpi--stock">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Livestock groups') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['livestock_groups'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--production">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Animals') }}</div>
                            <div class="farm-kpi__value">{{ number_format($stats['animals'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--sales">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Milk yield') }}</div>
                            <div class="farm-kpi__value">
                                {{ number_format($stats['liter_yield'] ?? 0, 0) }}
                                <span class="farm-kpi__unit">L</span>
                            </div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--revenue">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Milk sold') }}</div>
                            <div class="farm-kpi__value">
                                {{ number_format($stats['liters_sold'] ?? 0, 0) }}
                                <span class="farm-kpi__unit">L</span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="farm-kpi farm-kpi--health">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Animals sold') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['animals_sold'] ?? 0) }}</div>
                    </div>
                </div>

                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Sales') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['sales'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Money and health') }}">
            <h2 class="admin-farms__section-title">{{ __('Money and health') }}</h2>
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Revenue') }}</div>
                        <div class="farm-kpi__value">{{ number_format($money['revenue']) }} <span class="farm-kpi__unit">RWF</span></div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ __('Completed sales') }} · {{ $filters['label'] ?? __('All time') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Expenses') }}</div>
                        <div class="farm-kpi__value">{{ number_format($money['expenses']) }} <span class="farm-kpi__unit">RWF</span></div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ __('Paid expenses') }} · {{ $filters['label'] ?? __('All time') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'finance'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Unpaid balance') }}</div>
                        <div class="farm-kpi__value">{{ number_format($money['receivable']) }} <span class="farm-kpi__unit">RWF</span></div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ number_format($money['unpaid_sales']) }} {{ __('unpaid sales') }}, {{ number_format($money['overdue_sales']) }} {{ __('over 30 days') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Vaccinations due') }}</div>
                        <div class="farm-kpi__value">{{ number_format($health['vaccinations_due']) }}</div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ __('Due within 30 days or overdue') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Open disease cases') }}</div>
                        <div class="farm-kpi__value">{{ number_format($health['open_disease_cases']) }}</div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ number_format($health['disease_cases']) }} {{ __('diagnosed in period') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Sick animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($health['sick_animals']) }}</div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ $isPoultry ? __('Animal register only, not flocks') : number_format($health['sick']).' '.__('sick').', '.number_format($health['under_treatment']).' '.__('under treatment').', '.number_format($health['quarantined']).' '.__('quarantined') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Deaths') }}</div>
                        <div class="farm-kpi__value">{{ number_format($health['deaths']) }}</div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ __('In period') }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Treatments') }}</div>
                        <div class="farm-kpi__value">{{ number_format($health['treatments']) }}</div>
                        <div class="farm-kpi__hint" style="font-size:.75rem;color:#808080;">{{ __('Started in period') }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Farm details') }}">
            <div class="farm-dash__charts farm-dash__charts--3">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Account') }}</h2>
                        </div>
                    </header>
                    <dl class="admin-farms__detail">
                        @include('modules.farms._detail-row', ['label' => __('Login'), 'value' => $workspaceAccount])
                        @include('modules.farms._detail-row', ['label' => __('Name'), 'value' => $workspaceUser?->name])
                        @if ($farm->requiresOrganizationDetails())
                            @include('modules.farms._detail-row', ['label' => __('Organization'), 'value' => $farm->organization_name])
                            @include('modules.farms._detail-row', ['label' => __('Tax ID'), 'value' => $farm->tax_id])
                        @else
                            @include('modules.farms._detail-row', ['label' => __('Owner'), 'value' => $farm->owner_full_name])
                            @include('modules.farms._detail-row', ['label' => __('National ID'), 'value' => $farm->owner_national_id])
                        @endif
                        @include('modules.farms._detail-row', ['label' => __('Phone'), 'value' => $farm->contact_phone])
                        @include('modules.farms._detail-row', ['label' => __('Email'), 'value' => $farm->contact_email])
                    </dl>
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Registration') }}</h2>
                        </div>
                    </header>
                    <dl class="admin-farms__detail">
                        @include('modules.farms._detail-row', ['label' => __('Number'), 'value' => $farm->registration_number])
                        @include('modules.farms._detail-row', ['label' => __('Farm size'), 'value' => $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2).' ha' : null])
                        @include('modules.farms._detail-row', ['label' => __('Date'), 'value' => $farm->registration_date?->format('M j, Y')])
                        @include('modules.farms._detail-row', ['label' => __('Ownership'), 'value' => $ownershipLabel])
                        @include('modules.farms._detail-row', ['label' => __('Workspace'), 'value' => $farm->tenant_id])
                        @include('modules.farms._detail-row', ['label' => __('Joined'), 'value' => $farm->created_at?->format('M j, Y')])
                    </dl>
                </article>

                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Location') }}</h2>
                        </div>
                    </header>
                    <dl class="admin-farms__detail">
                        @include('modules.farms._detail-row', ['label' => __('Province'), 'value' => $farm->province])
                        @include('modules.farms._detail-row', ['label' => __('District'), 'value' => $farm->district])
                        @include('modules.farms._detail-row', ['label' => __('Sector'), 'value' => $farm->sector])
                        @include('modules.farms._detail-row', ['label' => __('Cell'), 'value' => $farm->cell])
                        @include('modules.farms._detail-row', ['label' => __('Village'), 'value' => $farm->village])
                    </dl>
                </article>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Workspace users') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Workspace users') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($wsUsers->count()) }} {{ __('total') }}. {{ __('Users belong to the whole workspace, not to a single farm.') }}</p>
                    </div>
                </header>
                @if ($wsUsers->isEmpty())
                    <p class="dash-empty">{{ __('No users in this workspace.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table health-table--compact">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Last sign in') }}</th>
                                    <th>{{ __('Added') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wsUsers as $wsUser)
                                    @php $login = $wsUser->last_login_at ? \Illuminate\Support\Carbon::parse($wsUser->last_login_at) : null; @endphp
                                    <tr>
                                        <td><span class="health-table__value">{{ $wsUser->name }}</span></td>
                                        <td><span class="health-table__meta">{{ $wsUser->email }}</span></td>
                                        <td><span class="health-table__value">{{ in_array($wsUser->role, \App\Services\WorkspaceAccessService::ADMIN_ROLES, true) ? __('Workspace admin') : __('Member') }}</span></td>
                                        <td>
                                            <span class="health-table__pill health-table__pill--{{ ($wsUser->is_active ?? true) ? 'ok' : 'muted' }}">{{ ($wsUser->is_active ?? true) ? __('Active') : __('Inactive') }}</span>
                                        </td>
                                        <td>
                                            @if ($login)
                                                <span class="health-table__value" title="{{ $login->format('M j, Y H:i') }}">{{ $login->diffForHumans() }}</span>
                                            @else
                                                <span class="health-table__meta">{{ __('Never') }}</span>
                                            @endif
                                        </td>
                                        <td><span class="health-table__meta">{{ $wsUser->created_at?->format('M j, Y') ?? '—' }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Recent activity and livestock groups') }}">
            <div class="farm-dash__charts farm-dash__charts--2" style="align-items: start;">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recent activity') }}</h2>
                        <p class="farm-panel__desc">{{ __('The 10 latest records entered for this farm, newest first. Not filtered by period.') }}</p>
                    </div>
                </header>
                @if ($overview['activity']->isEmpty())
                    <p class="dash-empty">{{ __('No records have been entered for this farm yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table health-table--compact">
                            <thead>
                                <tr>
                                    <th>{{ __('When') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th>{{ __('Details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($overview['activity'] as $item)
                                    <tr>
                                        <td style="white-space: nowrap;"><span class="health-table__meta" title="{{ $item['at']->format('M j, Y H:i') }}">{{ $item['at']->diffForHumans(['short' => true]) }}</span></td>
                                        <td style="white-space: nowrap;"><span class="health-table__pill health-table__pill--{{ $item['tone'] }}">{{ __($item['type']) }}</span></td>
                                        <td><span class="health-table__value">{{ $item['text'] }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Livestock groups') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($livestockGroups->count()) }} · {{ $filters['label'] ?? __('All time') }}</p>
                    </div>
                </header>
                @if ($livestockGroups->isEmpty())
                    <p class="dash-empty">{{ __('No livestock groups in this period.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table health-table--compact">
                            <thead>
                                <tr>
                                    <th>{{ __('Group') }}</th>
                                    <th>{{ __('Breed') }}</th>
                                    <th>{{ __('Head') }}</th>
                                    <th>{{ __('Animals') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($livestockGroups as $group)
                                    @php
                                        $groupTone = $group->status === 'active' ? 'ok' : 'muted';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'livestock', 'tone' => $groupTone])
                                                <span class="health-table__title">{{ $group->name }}</span>
                                            </div>
                                        </td>
                                        <td><span class="health-table__value">{{ $group->breed ?: '—' }}</span></td>
                                        <td><span class="health-table__value">{{ number_format($group->head_count) }}</span></td>
                                        <td><span class="health-table__value">{{ number_format($group->animals_count) }}</span></td>
                                        <td>
                                            <span class="health-table__pill health-table__pill--{{ $groupTone }}">{{ $group->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
            </div>
        </section>

        @if ($farm->requiresMembers() && $farm->members->isNotEmpty())
            <section class="farm-dash__section" aria-label="{{ __('Members') }}">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Members') }}</h2>
                            <p class="farm-panel__desc">{{ number_format($farm->members->count()) }} {{ __('total') }}</p>
                        </div>
                    </header>
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Phone') }}</th>
                                    <th>{{ __('Gender') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($farm->members as $member)
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'customer', 'tone' => 'default'])
                                                <span class="health-table__title">{{ trim($member->first_name.' '.$member->last_name) }}</span>
                                            </div>
                                        </td>
                                        <td><span class="health-table__meta">{{ $member->phone ?: '—' }}</span></td>
                                        <td>
                                            <span class="health-table__value">
                                                {{ $member->gender ? (config('modules.animal_genders')[$member->gender] ?? ucfirst($member->gender)) : '—' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            </section>
        @endif

        @if ($farm->notes)
            <section class="farm-dash__section" aria-label="{{ __('Notes') }}">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('Notes') }}</h2>
                        </div>
                    </header>
                    <p class="admin-farms__notes">{{ $farm->notes }}</p>
                </article>
            </section>
        @endif
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-farms__title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
            margin-top: 0.35rem;
        }
        .admin-farms__detail {
            margin: 0;
            padding: 0 1.25rem 1.25rem;
        }
        .admin-farms__notes {
            margin: 0;
            padding: 0 1.25rem 1.25rem;
            font-size: 0.875rem;
            color: #374151;
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .admin-farms .dash-back-link {
            display: inline-block;
            margin-bottom: 0.15rem;
        }
        .admin-farms .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-farms .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('admin_farm_filter_period')?.addEventListener('change', function () {
            document.getElementById('admin-farm-custom-dates')?.classList.toggle('dash-ops-field--muted', this.value !== 'custom');
        });
    </script>
@endpush

