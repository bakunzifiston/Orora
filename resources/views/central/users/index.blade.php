@extends('layouts.admin')

@section('title', __('Farms'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-farms">
        @if (empty($farmsReady))
            <article class="farm-panel">
                <p class="dash-empty">{{ __('Farm tables are not set up yet. Run php artisan migrate --force.') }}</p>
            </article>
        @else
            @include('central.users.partials.toolbar')

            @include('central.partials.platform-kpis', ['filters' => $filters, 'hideAccountKpis' => true])

            <section class="farm-dash__section" aria-label="{{ __('All farms') }}">
                <article class="farm-panel">
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ __('All farms') }}</h2>
                            <p class="farm-panel__desc">{{ number_format($farms->total()) }} {{ __('total') }}</p>
                        </div>
                    </header>

                    @if ($farms->isEmpty())
                        <p class="dash-empty">
                            @if (! empty($filtersActive))
                                {{ __('No farms match the selected filters.') }}
                            @else
                                {{ __('No farms registered yet.') }}
                            @endif
                        </p>
                    @else
                        <div class="dash-table-wrap">
                            <table class="health-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Farm') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Owner') }}</th>
                                        <th>{{ __('Location') }}</th>
                                        <th>{{ __('Account') }}</th>
                                        <th>{{ __('Stock') }}</th>
                                        <th>{{ __('Registered') }}</th>
                                        <th class="health-table__actions">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($farms as $farm)
                                        @php
                                            $isPoultry = ($farm->primary_species ?? null) === 'poultry';
                                            $statusTone = match ($farm->status) {
                                                'active' => 'ok',
                                                'pending' => 'warn',
                                                'suspended' => 'bad',
                                                default => 'muted',
                                            };
                                            $owner = $farm->requiresOrganizationDetails()
                                                ? ($farm->organization_name ?: '—')
                                                : ($farm->owner_full_name ?: '—');
                                            $accountEmail = $accountEmails[$farm->tenant_id] ?? null;
                                            $farmQuery = http_build_query(request()->only(['period', 'from', 'to', 'farm_id', 'province_code', 'district_code']));
                                            $showUrl = route('central.farms.show', $farm).($farmQuery ? '?'.$farmQuery : '');
                                            $stockLabel = $isPoultry
                                                ? number_format($farm->flocks_count ?? 0).' '.__('flocks')
                                                : number_format($farm->animals_count).' '.__('animals');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="health-table__primary">
                                                    @include('modules.health.partials.table-icon', ['icon' => 'farm', 'tone' => $statusTone])
                                                    <div class="health-table__stack">
                                                        <a href="{{ $showUrl }}" class="health-table__title health-table__title--link">{{ $farm->name }}</a>
                                                        @if ($farm->status)
                                                            <span class="health-table__meta">{{ ucfirst($farm->status) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="health-table__pill health-table__pill--{{ $isPoultry ? 'warn' : 'ok' }}">
                                                    {{ $isPoultry ? __('Poultry') : __('Cattle') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ $owner }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ Str::limit($farm->location_label ?: '—', 42) }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">{{ $accountEmail ?: '—' }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__value">{{ $stockLabel }}</span>
                                            </td>
                                            <td>
                                                <span class="health-table__meta">{{ $farm->created_at?->format('M j, Y') ?? '—' }}</span>
                                            </td>
                                            <td class="health-table__actions">
                                                <div class="health-table__action-btns">
                                                    <a href="{{ $showUrl }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="dash-pagination">{{ $farms->links() }}</div>
                    @endif
                </article>
            </section>
        @endif
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-farms .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-farms .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
        .admin-farms .health-table__action-btns .dash-btn-save--sm {
            padding: 0.4rem 0.75rem;
            font-size: 0.75rem;
        }
    </style>
@endpush

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

                districtSelect.innerHTML = '<option value="">{{ __('All districts') }}</option>';

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
