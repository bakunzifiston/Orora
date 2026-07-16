@extends('layouts.admin')

@section('title', 'Farms')

@section('content')
    @if (empty($farmsReady))
        <div class="dash-panel">
            <p class="dash-empty">Farm tables are not set up yet. Run <code>php artisan migrate --force</code>.</p>
        </div>
    @else
        @include('central.users.partials.toolbar')

        @if (! empty($filtersActive))
            <section class="dash-ops-row" aria-label="Filtered summary" style="margin-bottom: 1.25rem;">
                @include('central.partials.platform-kpis', ['filters' => $filters, 'hideAccountKpis' => true])
            </section>
        @endif

        <div class="dash-panel dash-panel--flush dash-data-table-panel">
            <div class="admin-panel-head" style="padding: 1.25rem 1.25rem 0;">
                <h2 class="dash-panel-title">All farms</h2>
                <span class="admin-panel-meta">{{ number_format($farms->total()) }}</span>
            </div>

            @if ($farms->isEmpty())
                <p class="dash-data-table__empty">
                    @if (! empty($filtersActive))
                        No farms match the selected filters.
                    @else
                        No farms registered yet.
                    @endif
                </p>
            @else
                <div class="dash-data-table-wrap">
                    <table class="dash-data-table">
                        <thead>
                            <tr>
                                <th>Farm</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th>Account</th>
                                <th class="dash-data-table__num">Groups</th>
                                <th class="dash-data-table__num">Animals</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($farms as $farm)
                                @php
                                    $statusBadge = match ($farm->status) {
                                        'active' => 'dash-data-table__badge--active',
                                        'pending' => 'dash-data-table__badge--pending',
                                        'suspended' => 'dash-data-table__badge--suspended',
                                        default => 'dash-data-table__badge--inactive',
                                    };
                                    $owner = $farm->requiresOrganizationDetails()
                                        ? ($farm->organization_name ?: '—')
                                        : ($farm->owner_full_name ?: '—');
                                    $accountEmail = $accountEmails[$farm->tenant_id] ?? null;
                                    $farmQuery = http_build_query(request()->only(['period', 'from', 'to', 'farm_id', 'province_code', 'district_code']));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="dash-data-table__primary">
                                            <div class="dash-data-table__title-row">
                                                <a href="{{ route('central.users.show', $farm).($farmQuery ? '?'.$farmQuery : '') }}" class="dash-data-table__link">{{ $farm->name }}</a>
                                                @if ($farm->status)
                                                    <span class="dash-data-table__badge {{ $statusBadge }}">{{ $farm->status }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="dash-data-table__muted">{{ $owner }}</td>
                                    <td class="dash-data-table__muted">{{ Str::limit($farm->location_label ?: '—', 42) }}</td>
                                    <td class="dash-data-table__muted">{{ $accountEmail ?: '—' }}</td>
                                    <td class="dash-data-table__num">{{ number_format($farm->livestock_count) }}</td>
                                    <td class="dash-data-table__num">{{ number_format($farm->animals_count) }}</td>
                                    <td class="dash-data-table__muted">{{ $farm->created_at?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="dash-pagination">{{ $farms->links() }}</div>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const periodSelect = document.getElementById('admin_users_filter_period');
            const customDates = document.getElementById('admin-users-custom-dates');
            const farmSelect = document.getElementById('admin_users_filter_farm');
            const provinceSelect = document.getElementById('admin_users_filter_province');
            const districtSelect = document.getElementById('admin_users_filter_district');
            const apiBase = @json(url('/admin/api/rwanda'));

            periodSelect?.addEventListener('change', function () {
                customDates?.classList.toggle('dash-ops-field--muted', this.value !== 'custom');
            });

            function setLocationDisabled(disabled) {
                if (provinceSelect) {
                    provinceSelect.disabled = disabled;
                    if (disabled) {
                        provinceSelect.value = '';
                    }
                }
                if (districtSelect) {
                    districtSelect.disabled = disabled || !provinceSelect?.value;
                    if (disabled) {
                        districtSelect.value = '';
                    }
                }
            }

            farmSelect?.addEventListener('change', function () {
                setLocationDisabled(this.value !== '');
            });

            async function loadDistricts(provinceCode, selected) {
                if (!districtSelect) {
                    return;
                }

                districtSelect.innerHTML = '<option value="">All districts</option>';

                if (!provinceCode) {
                    districtSelect.disabled = true;
                    districtSelect.value = '';
                    return;
                }

                try {
                    const response = await fetch(`${apiBase}/districts?province_code=${encodeURIComponent(provinceCode)}`, {
                        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) {
                        throw new Error('Failed to load districts');
                    }

                    const items = await response.json();
                    items.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.code;
                        option.textContent = item.name;
                        if (String(selected) === String(item.code)) {
                            option.selected = true;
                        }
                        districtSelect.appendChild(option);
                    });
                    districtSelect.disabled = farmSelect?.value !== '';
                } catch (error) {
                    districtSelect.disabled = true;
                }
            }

            provinceSelect?.addEventListener('change', function () {
                districtSelect.value = '';
                loadDistricts(this.value, '');
            });

            if (provinceSelect?.value && districtSelect) {
                loadDistricts(provinceSelect.value, districtSelect.value);
            }
        })();
    </script>
@endpush
