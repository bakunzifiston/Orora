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
    @endphp

    @include('central.partials.filter-toolbar', [
        'toolbarTitle' => $farm->name,
        'toolbarSubtitle' => 'Farm activity for',
        'toolbarAction' => route('central.users.show', $farm),
        'toolbarFormId' => 'admin-farm-filters-form',
        'toolbarPeriodId' => 'admin_farm_filter_period',
        'toolbarDatesId' => 'admin-farm-custom-dates',
    ])

    <div class="dash-page-header" style="margin: -0.5rem 0 1.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
            <span class="dash-farm-card__badge {{ $statusClass }}">{{ ucfirst($farm->status) }}</span>
            <span style="font-size: 0.8125rem; color: var(--orora-gray);">
                Workspace: <code>{{ $farm->tenant_id }}</code>
                @if ($tenant?->name)
                    · {{ $tenant->name }}
                @endif
            </span>
            @if ($farm->registration_number)
                <span style="font-size: 0.8125rem; color: var(--orora-gray);">{{ $farm->registration_number }}</span>
            @endif
        </div>
        <a href="{{ $backUrl }}" class="dash-back-link">← Back to farms</a>
    </div>

    <section class="dash-ops-row" aria-label="Farm summary" style="margin-bottom: 1.25rem;">
        <div class="dash-stats">
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Farm size</div>
                    <div class="dash-stat-value">{{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2).' ha' : '—' }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Registered farm area</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'farm', 'label' => 'Farm size'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Livestock groups</div>
                    <div class="dash-stat-value accent">{{ number_format($stats['livestock_groups']) }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Groups added in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'livestock', 'label' => 'Livestock groups'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Head count</div>
                    <div class="dash-stat-value">{{ number_format($stats['head_count']) }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Head in groups added in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'feeding', 'label' => 'Head count'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Animals registered</div>
                    <div class="dash-stat-value accent">{{ number_format($stats['animals']) }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Animals registered in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'animal', 'label' => 'Animals'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Liter yield</div>
                    <div class="dash-stat-value accent">{{ number_format($stats['liter_yield'], 0) }} <span class="dash-home-stat__suffix">L</span></div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Completed milk sessions in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'milk', 'label' => 'Liter yield'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Liters sold</div>
                    <div class="dash-stat-value">{{ number_format($stats['liters_sold'], 0) }} <span class="dash-home-stat__suffix">L</span></div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Completed milk sales in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Liters sold'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Certificates</div>
                    <div class="dash-stat-value">{{ number_format($stats['certificates']) }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Issued in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'certificate', 'label' => 'Certificates'])
            </div>
            <div class="dash-stat-card dash-ops-kpi">
                <div>
                    <div class="dash-stat-label">Sales</div>
                    <div class="dash-stat-value">{{ number_format($stats['sales']) }}</div>
                    <p class="dash-field-hint" style="margin: 0.2rem 0 0;">Transactions in period</p>
                </div>
                @include('modules.partials.stat-icon', ['icon' => 'sale', 'label' => 'Sales'])
            </div>
        </div>
    </section>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Farm owner & account</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => 'Login email', 'value' => $workspaceAccount])
                @include('modules.farms._detail-row', ['label' => 'Account name', 'value' => $workspaceUser?->name])
                @if ($farm->requiresOrganizationDetails())
                    @include('modules.farms._detail-row', ['label' => 'Organization', 'value' => $farm->organization_name])
                    @include('modules.farms._detail-row', ['label' => 'Tax ID', 'value' => $farm->tax_id])
                @else
                    @include('modules.farms._detail-row', ['label' => 'Owner', 'value' => $farm->owner_full_name])
                    @include('modules.farms._detail-row', ['label' => 'National ID', 'value' => $farm->owner_national_id])
                    @include('modules.farms._detail-row', ['label' => 'Gender', 'value' => $farm->owner_gender ? (config('modules.animal_genders')[$farm->owner_gender] ?? ucfirst($farm->owner_gender)) : null])
                @endif
                @include('modules.farms._detail-row', ['label' => 'Phone', 'value' => $farm->contact_phone])
                @include('modules.farms._detail-row', ['label' => 'Email', 'value' => $farm->contact_email])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Registration</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => 'Registration no.', 'value' => $farm->registration_number])
                @include('modules.farms._detail-row', ['label' => 'Registration date', 'value' => $farm->registration_date?->format('M j, Y')])
                @include('modules.farms._detail-row', ['label' => 'Status', 'value' => ucfirst($farm->status)])
                @include('modules.farms._detail-row', ['label' => 'Ownership', 'value' => $ownershipLabel])
                @include('modules.farms._detail-row', ['label' => 'Registered on platform', 'value' => $farm->created_at?->format('M j, Y g:i A')])
            </dl>
        </div>

        <div class="dash-panel">
            <div class="dash-panel-title">Location</div>
            <dl class="dash-farm-detail">
                @include('modules.farms._detail-row', ['label' => 'Country', 'value' => $farm->country])
                @include('modules.farms._detail-row', ['label' => 'Province', 'value' => $farm->province])
                @include('modules.farms._detail-row', ['label' => 'District', 'value' => $farm->district])
                @include('modules.farms._detail-row', ['label' => 'Sector', 'value' => $farm->sector])
                @include('modules.farms._detail-row', ['label' => 'Cell', 'value' => $farm->cell])
                @include('modules.farms._detail-row', ['label' => 'Village', 'value' => $farm->village])
            </dl>
        </div>
    </div>

    <div class="dash-panel dash-panel--flush" style="margin-top: 1.25rem;">
        <div class="dash-panel-head">
            <h2 class="dash-panel-title">Livestock groups</h2>
            <span class="dash-field-hint" style="margin: 0;">{{ number_format($livestockGroups->count()) }} added in {{ $filters['label'] }}</span>
        </div>
        @if ($livestockGroups->isEmpty())
            <p class="dash-data-table__empty">No livestock groups added in this period.</p>
        @else
            <div class="dash-data-table-wrap">
                <table class="dash-data-table">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Breed</th>
                            <th class="dash-data-table__num">Head count</th>
                            <th class="dash-data-table__num">Animals in period</th>
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
                                <td>
                                    <span class="dash-data-table__text">{{ $group->name }}</span>
                                </td>
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
        <div class="dash-panel dash-panel--flush" style="margin-top: 1rem;">
            <div class="dash-panel-head">
                <h2 class="dash-panel-title">Members</h2>
            </div>
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
        <div class="dash-panel" style="margin-top: 1rem;">
            <div class="dash-panel-title">Notes</div>
            <p style="margin: 0; font-size: 0.875rem; color: #374151; white-space: pre-wrap;">{{ $farm->notes }}</p>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.getElementById('admin_farm_filter_period')?.addEventListener('change', function () {
            const custom = this.value === 'custom';
            document.getElementById('admin-farm-custom-dates')?.classList.toggle('dash-ops-field--muted', !custom);
        });
    </script>
@endpush
