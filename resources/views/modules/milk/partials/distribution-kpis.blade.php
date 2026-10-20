@php
    $totals = $distribution['totals'];
    $filters = $distribution['filters'];
    $unaccounted = $distribution['unaccounted'];
    $cards = [
        ['label' => __('Fed to calves'), 'value' => $totals['fed_to_calf'] ?? 0, 'icon' => 'animal', 'tone' => 'breeding', 'category' => 'fed_to_calf'],
        ['label' => __('Household'), 'value' => $totals['household'] ?? 0, 'icon' => 'farm', 'tone' => 'farms', 'category' => 'household'],
        ['label' => __('Given away'), 'value' => $distribution['given_away'], 'icon' => 'customer', 'tone' => 'feeding', 'category' => null],
        ['label' => __('Other use'), 'value' => $totals['other'] ?? 0, 'icon' => 'box', 'tone' => 'stock', 'category' => 'other'],
    ];
@endphp

<section class="farm-dash__section" aria-label="{{ __('Milk distribution') }}" data-milk-distribution-kpis>
    <div class="farm-dash__kpis farm-dash__kpis--5">
        @include('modules.milk.partials.cost-kpi', ['costCurrent' => $costCurrent])
        @foreach ($cards as $card)
            <a
                href="{{ route('milk.distribution', $card['category'] ? $filters + ['category' => $card['category']] : $filters) }}"
                class="farm-kpi farm-kpi--{{ $card['tone'] }}"
            >
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => $card['icon']])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ $card['label'] }}</div>
                    <div class="farm-kpi__value">
                        {{ number_format($card['value'], 0) }}
                        <span class="farm-kpi__unit">L</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <p class="dash-field-hint" style="margin-top: 0.6rem;">
        @if ($unaccounted > 0)
            {{ __(':litres L of the yield is not yet accounted for.', ['litres' => number_format($unaccounted, 0)]) }}
            <a href="{{ route('milk.distribution.create') }}" style="font-weight: 600; text-decoration: underline;">{{ __('Record milk use') }}</a>
        @elseif ($unaccounted < 0)
            {{ __(':litres L more was distributed than produced in this period — check stored milk from earlier periods or the records.', ['litres' => number_format(abs($unaccounted), 0)]) }}
        @else
            {{ __('All milk produced in this period is accounted for.') }}
        @endif
        · <a href="{{ route('milk.distribution', $filters) }}" style="font-weight: 600; text-decoration: underline;">{{ __('View all distribution') }}</a>
    </p>
</section>
