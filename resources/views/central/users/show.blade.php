@extends('layouts.admin')

@section('title', $farm->name)

@section('content')
    @php
        $statusClass = match ($farm->status) {
            'active' => 'dash-farm-card__badge--active',
            'pending' => 'dash-farm-card__badge--pending',
            'suspended' => 'dash-farm-card__badge--suspended',
            default => 'dash-farm-card__badge--inactive',
        };
        $ownershipLabel = config('modules.ownership_types')[$farm->ownership_type] ?? ucfirst(str_replace('_', ' ', (string) $farm->ownership_type));
        $backQuery = http_build_query(request()->only(['period', 'from', 'to', 'farm_id', 'province_code', 'district_code']));
        $backUrl = route('central.users.index').($backQuery ? '?'.$backQuery : '');
        $location = collect([$farm->district, $farm->province, $farm->country])->filter()->implode(', ');
    @endphp

    <div class="admin-farm-page">
        <div class="admin-farm-page__top">
            <div>
                <a href="{{ $backUrl }}" class="dash-back-link">← Farms</a>
                <div class="admin-farm-page__title-row">
                    <h1 class="dash-welcome">{{ $farm->name }}</h1>
                    <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($farm->status) }}</span>
                </div>
                <p class="admin-farm-page__meta">
                    {{ $location ?: 'Rwanda' }}
                    @if ($farm->registration_number)
                        · {{ $farm->registration_number }}
                    @endif
                </p>
            </div>

            <form method="GET" action="{{ route('central.users.show', $farm) }}" class="admin-farm-page__filters" id="admin-farm-filters-form">
                <div class="dash-ops-field">
                    <label for="admin_farm_filter_period">Period</label>
                    <select name="period" id="admin_farm_filter_period">
                        <option value="all" @selected(($filters['period'] ?? 'all') === 'all' || ($filters['period'] ?? '') === '')>All time</option>
                        <option value="daily" @selected(($filters['period'] ?? '') === 'daily')>Daily</option>
                        <option value="monthly" @selected(($filters['period'] ?? '') === 'monthly')>Monthly</option>
                        <option value="yearly" @selected(($filters['period'] ?? '') === 'yearly')>Yearly</option>
                        <option value="custom" @selected(($filters['period'] ?? '') === 'custom')>Custom</option>
                    </select>
                </div>
                <div class="dash-ops-field dash-ops-field--dates @if(($filters['period'] ?? 'all') !== 'custom') dash-ops-field--muted @endif" id="admin-farm-custom-dates">
                    <label>Range</label>
                    <div class="dash-ops-dates">
                        <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" aria-label="From date">
                        <span class="dash-ops-dates__sep">→</span>
                        <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" aria-label="To date">
                    </div>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">Apply</button>
            </form>
        </div>

        <section class="dash-ops-row" aria-label="Farm summary">
            <div class="dash-stats admin-kpis">
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Size</div>
                        <div class="dash-stat-value">{{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2).' ha' : '—' }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farm size'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Groups</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['livestock_groups']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'livestock', 'label' => 'Livestock groups'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Animals</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['animals']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Milk yield</div>
                        <div class="dash-stat-value accent">{{ number_format($stats['liter_yield'], 0) }}<span class="dash-home-stat__suffix"> L</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Milk yield'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Milk sold</div>
                        <div class="dash-stat-value">{{ number_format($stats['liters_sold'], 0) }}<span class="dash-home-stat__suffix"> L</span></div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Milk sold'])
                </div>
                <div class="dash-stat-card dash-ops-kpi">
                    <div>
                        <div class="dash-stat-label">Sales</div>
                        <div class="dash-stat-value">{{ number_format($stats['sales']) }}</div>
                    </div>
                    @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Sales'])
                </div>
            </div>
        </section>

        <div class="dash-health-grid">
            <div class="dash-panel">
                <h2 class="dash-panel-title">Account</h2>
                <dl class="dash-farm-detail">
                    @include('modules.farms._detail-row', ['label' => 'Login', 'value' => $workspaceAccount])
                    @include('modules.farms._detail-row', ['label' => 'Name', 'value' => $workspaceUser?->name])
                    @if ($farm->requiresOrganizationDetails())
                        @include('modules.farms._detail-row', ['label' => 'Organization', 'value' => $farm->organization_name])
                        @include('modules.farms._detail-row', ['label' => 'Tax ID', 'value' => $farm->tax_id])
                    @else
                        @include('modules.farms._detail-row', ['label' => 'Owner', 'value' => $farm->owner_full_name])
                        @include('modules.farms._detail-row', ['label' => 'National ID', 'value' => $farm->owner_national_id])
                    @endif
                    @include('modules.farms._detail-row', ['label' => 'Phone', 'value' => $farm->contact_phone])
                    @include('modules.farms._detail-row', ['label' => 'Email', 'value' => $farm->contact_email])
                </dl>
            </div>

            <div class="dash-panel">
                <h2 class="dash-panel-title">Registration</h2>
                <dl class="dash-farm-detail">
                    @include('modules.farms._detail-row', ['label' => 'Number', 'value' => $farm->registration_number])
                    @include('modules.farms._detail-row', ['label' => 'Date', 'value' => $farm->registration_date?->format('M j, Y')])
                    @include('modules.farms._detail-row', ['label' => 'Ownership', 'value' => $ownershipLabel])
                    @include('modules.farms._detail-row', ['label' => 'Workspace', 'value' => $farm->tenant_id])
                    @include('modules.farms._detail-row', ['label' => 'Joined', 'value' => $farm->created_at?->format('M j, Y')])
                </dl>
            </div>

            <div class="dash-panel">
                <h2 class="dash-panel-title">Location</h2>
                <dl class="dash-farm-detail">
                    @include('modules.farms._detail-row', ['label' => 'Province', 'value' => $farm->province])
                    @include('modules.farms._detail-row', ['label' => 'District', 'value' => $farm->district])
                    @include('modules.farms._detail-row', ['label' => 'Sector', 'value' => $farm->sector])
                    @include('modules.farms._detail-row', ['label' => 'Cell', 'value' => $farm->cell])
                    @include('modules.farms._detail-row', ['label' => 'Village', 'value' => $farm->village])
                </dl>
            </div>
        </div>

        <div class="dash-panel dash-panel--flush">
            <div class="admin-panel-head">
                <h2 class="dash-panel-title">Livestock groups</h2>
                <span class="admin-panel-meta">{{ number_format($livestockGroups->count()) }} · {{ $filters['label'] }}</span>
            </div>
            @if ($livestockGroups->isEmpty())
                <p class="dash-empty">No livestock groups in this period.</p>
            @else
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>Group</th>
                                <th>Breed</th>
                                <th class="dash-data-table__num">Head</th>
                                <th class="dash-data-table__num">Animals</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($livestockGroups as $group)
                                @php
                                    $groupBadge = $group->status === 'active'
                                        ? 'dash-data-table__badge--active'
                                        : 'dash-data-table__badge--inactive';
                                @endphp
                                <tr>
                                    <td>{{ $group->name }}</td>
                                    <td class="dash-data-table__muted">{{ $group->breed ?: '—' }}</td>
                                    <td class="dash-data-table__num">{{ number_format($group->head_count) }}</td>
                                    <td class="dash-data-table__num">{{ number_format($group->animals_count) }}</td>
                                    <td><span class="dash-data-table__badge {{ $groupBadge }}">{{ $group->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($farm->requiresMembers() && $farm->members->isNotEmpty())
            <div class="dash-panel dash-panel--flush">
                <h2 class="dash-panel-title">Members</h2>
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($farm->members as $member)
                                <tr>
                                    <td>{{ trim($member->first_name.' '.$member->last_name) }}</td>
                                    <td class="dash-data-table__muted">{{ $member->phone ?: '—' }}</td>
                                    <td class="dash-data-table__muted">{{ $member->gender ? (config('modules.animal_genders')[$member->gender] ?? ucfirst($member->gender)) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if ($farm->notes)
            <div class="dash-panel">
                <h2 class="dash-panel-title">Notes</h2>
                <p class="admin-farm-page__notes">{{ $farm->notes }}</p>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .admin-farm-page {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        .admin-farm-page__top {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem 1.5rem;
        }
        .admin-farm-page__title-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.65rem;
            margin-top: 0.35rem;
        }
        .admin-farm-page__title-row .dash-welcome {
            margin: 0;
        }
        .admin-farm-page__meta {
            margin: 0.35rem 0 0;
            font-size: 0.8125rem;
            color: var(--orora-gray);
        }
        .admin-farm-page__filters {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 0.75rem;
        }
        .admin-farm-page__notes {
            margin: 0;
            font-size: 0.875rem;
            color: #374151;
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .admin-farm-page .dash-panel-title {
            margin-bottom: 1rem;
        }
        .admin-farm-page .dash-panel--flush .dash-panel-title,
        .admin-farm-page .admin-panel-head .dash-panel-title {
            margin-bottom: 0;
        }
        .admin-farm-page .admin-panel-head {
            padding: 0 0 1rem;
        }
        .admin-farm-page .dash-panel--flush > .dash-panel-title {
            padding: 1.25rem 1.25rem 0;
        }
        .admin-farm-page .dash-panel--flush .admin-panel-head {
            padding: 1.25rem 1.25rem 1rem;
        }
        .admin-farm-page .dash-empty {
            padding: 0 1.25rem 1.25rem;
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
