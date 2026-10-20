@extends('layouts.milk-module')

@section('title', __('Milk — Distribution'))

@php
    $categoryLabels = config('modules.milk_distribution_category_labels');
    $categoryIcons = [
        'sold' => 'sale',
        'fed_to_calf' => 'animal',
        'household' => 'farm',
        'neighbor' => 'customer',
        'relative_visitor' => 'employee',
        'other' => 'box',
    ];
    $categoryTones = [
        'sold' => 'ok',
        'fed_to_calf' => 'warn',
        'household' => 'muted',
        'neighbor' => 'muted',
        'relative_visitor' => 'muted',
        'other' => 'muted',
    ];
    $filterParams = array_filter(['farm_id' => $selectedFarm, 'period' => $period]);
@endphp

@section('milk-content')
    <div class="farm-dash farms-page health-page">
        @include('modules.partials.header', [
            'title' => __('Milk distribution'),
            'subtitle' => __('Where the farm\'s milk goes: sold, fed to calves, used at home or given away.'),
            'createRoute' => 'milk.distribution.create',
            'createLabel' => '+ '.__('Record milk use'),
            'secondaryLinks' => [
                ['route' => 'sales.transactions.create', 'params' => ['type' => 'milk_sale'], 'label' => __('Record a milk sale')],
            ],
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('milk.distribution') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__brand">
                <span class="farm-dash__toolbar-meta">{{ $periodLabel }}</span>
            </div>
            <div class="dash-ops-toolbar__controls farms-page__filters">
                @include('modules.milk.partials.farm-filter', [
                    'farms' => $farms,
                    'selectedFarm' => $selectedFarm,
                ])
                <div class="dash-ops-field">
                    <label for="milk_dist_category">{{ __('Use') }}</label>
                    <select name="category" id="milk_dist_category" onchange="this.form.submit()">
                        <option value="">{{ __('All uses') }}</option>
                        @foreach ($categoryLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedCategory === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="milk_dist_period">{{ __('Period') }}</label>
                    <select name="period" id="milk_dist_period" onchange="this.form.submit()">
                        <option value="today" @selected($period === 'today')>{{ __('Today') }}</option>
                        <option value="monthly" @selected($period === 'monthly')>{{ __('This month') }}</option>
                        <option value="yearly" @selected($period === 'yearly')>{{ __('This year') }}</option>
                        <option value="all" @selected($period === 'all')>{{ __('All time') }}</option>
                    </select>
                </div>
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('By use') }}">
            <div class="farm-dash__kpis farm-dash__kpis--5">
                @php
                    $useTotals = collect($totals)->except('sold');
                    $useSum = $useTotals->sum();
                @endphp
                @foreach ($useTotals as $category => $litres)
                    @php
                        $isSelected = $selectedCategory === $category;
                        $share = $useSum > 0 ? round($litres / $useSum * 100) : 0;
                    @endphp
                    <a
                        href="{{ route('milk.distribution', $isSelected ? $filterParams : $filterParams + ['category' => $category]) }}"
                        class="farm-kpi {{ $isSelected ? 'farm-kpi--selected' : '' }}"
                        aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                    >
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => $categoryIcons[$category] ?? 'box'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __($categoryLabels[$category] ?? $category) }}</div>
                            <div class="farm-kpi__value">
                                {{ number_format($litres, 1) }} <span class="farm-kpi__unit">L · {{ $share }}%</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($distributions->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                </div>
                <p class="dash-empty">{{ __('No milk distribution recorded for this selection.') }}</p>
                <a href="{{ route('milk.distribution.create') }}" class="dash-btn-save">{{ __('Record milk use') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Use') }}</th>
                                <th>{{ __('Recipient') }}</th>
                                <th>{{ __('Quantity') }}</th>
                                <th>{{ __('Farm / tank') }}</th>
                                <th>{{ __('Recorded by') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($distributions as $distribution)
                                <tr>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__title">{{ $distribution->distributed_at->format('M j, Y') }}</span>
                                            <span class="health-table__meta">
                                                {{ $distribution->isFromSale() ? $distribution->distribution_code : $distribution->distributed_at->format('H:i').' · '.$distribution->distribution_code }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span>
                                                <span class="health-table__pill health-table__pill--{{ $categoryTones[$distribution->category] ?? 'muted' }}">
                                                    {{ __($distribution->categoryLabel()) }}
                                                </span>
                                            </span>
                                            @if ($distribution->category === 'other' && $distribution->other_use)
                                                <span class="health-table__meta">{{ $distribution->other_use }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__title">{{ $distribution->recipientLabel() }}</span>
                                            <span class="health-table__meta">
                                                {{ __($distribution->recipientTypeLabel()) }}{{ $distribution->recipient_phone ? ' · '.$distribution->recipient_phone : '' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ number_format($distribution->quantity_liters, 2) }} L</span>
                                    </td>
                                    <td>
                                        <div class="health-table__stack">
                                            <span class="health-table__value">{{ $distribution->farm?->name }}</span>
                                            <span class="health-table__meta">{{ $distribution->storage?->container_name ?? __('Not from a tank') }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $distribution->recorder?->name ?? '—' }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @if ($distribution->isFromSale())
                                            <div class="health-table__action-btns">
                                                <a href="{{ route('sales.transactions.show', $distribution->sale_transaction_id) }}" class="dash-farm-card__btn">{{ __('View sale') }}</a>
                                            </div>
                                        @else
                                            @include('modules.health.partials.table-actions', [
                                                'model' => $distribution,
                                                'editRoute' => 'milk.distribution.edit',
                                                'destroyRoute' => 'milk.distribution.destroy',
                                                'deleteConfirm' => $distribution->milk_storage_id
                                                    ? __('Delete this record? The milk will be returned to the storage tank.')
                                                    : __('Delete this record?'),
                                            ])
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $distributions->links() }}</div>
        @endif
    </div>
@endsection
