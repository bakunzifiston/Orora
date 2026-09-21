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
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--farms">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Size') }}</div>
                        <div class="farm-kpi__value">
                            {{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2) : '—' }}
                            @if ($farm->farm_size_hectares !== null)
                                <span class="farm-kpi__unit">ha</span>
                            @endif
                        </div>
                    </div>
                </div>

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

        <section class="farm-dash__section" aria-label="{{ __('Livestock groups') }}">
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
                        <table class="health-table">
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
