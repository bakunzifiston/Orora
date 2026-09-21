@php
    $stats = $stats ?? [];
    $hideAccountKpis = (bool) ($hideAccountKpis ?? false);
    $periodLabel = $filters['label'] ?? null;
    $species = $stats['primary_species'] ?? null;
    $isPoultry = $species === 'poultry';
    $isCattle = $species === 'cattle' || $species === 'dairy';
    $showCattleStock = ! $isPoultry;
    $showPoultryStock = ! $isCattle;
    $showMilk = ! $isPoultry;
    $showEggs = ! $isCattle;
@endphp

<section class="farm-dash__section" aria-label="{{ __('Summary') }}">
    <div class="farm-dash__kpis farm-dash__kpis--4">
        @unless ($hideAccountKpis)
            <a href="{{ route('central.accounts.index', ['status' => 'pending']) }}" class="farm-kpi farm-kpi--receivable">
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Accounts pending') }}</div>
                    <div class="farm-kpi__value">{{ number_format($stats['accounts_without_farm'] ?? 0) }}</div>
                    <div class="farm-kpi__hint">{{ __('No farm yet') }}</div>
                </div>
            </a>
        @endunless

        <a href="{{ route('central.farms.index') }}" class="farm-kpi farm-kpi--farms">
            <div class="farm-kpi__icon" aria-hidden="true">
                @include('layouts.partials.dashboard-nav-icon', ['icon' => 'farm'])
            </div>
            <div class="farm-kpi__body">
                <div class="farm-kpi__label">{{ __('Farms') }}</div>
                <div class="farm-kpi__value">{{ number_format($stats['farms'] ?? 0) }}</div>
            </div>
        </a>

        @if ($showCattleStock)
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
        @endif

        @if ($showPoultryStock)
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
        @endif

        @if ($showMilk)
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
                    @if ($periodLabel)
                        <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                    @endif
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
                    @if ($periodLabel)
                        <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                    @endif
                </div>
            </div>
        @endif

        @if ($showEggs)
            <div class="farm-kpi farm-kpi--eggs">
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'milk'])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Eggs collected') }}</div>
                    <div class="farm-kpi__value">{{ number_format($stats['eggs_collected'] ?? 0) }}</div>
                    @if ($periodLabel)
                        <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                    @endif
                </div>
            </div>

            <div class="farm-kpi farm-kpi--revenue">
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Eggs sold') }}</div>
                    <div class="farm-kpi__value">{{ number_format($stats['eggs_sold'] ?? 0, 0) }}</div>
                    @if ($periodLabel)
                        <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                    @endif
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
                @if ($periodLabel)
                    <div class="farm-kpi__hint">{{ $periodLabel }}</div>
                @endif
            </div>
        </div>

        @unless ($isPoultry || $isCattle)
            <div class="farm-kpi farm-kpi--farms">
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Cattle farms') }}</div>
                    <div class="farm-kpi__value">{{ number_format($stats['cattle_farms'] ?? 0) }}</div>
                </div>
            </div>

            <div class="farm-kpi farm-kpi--eggs">
                <div class="farm-kpi__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'livestock'])
                </div>
                <div class="farm-kpi__body">
                    <div class="farm-kpi__label">{{ __('Poultry farms') }}</div>
                    <div class="farm-kpi__value">{{ number_format($stats['poultry_farms'] ?? 0) }}</div>
                </div>
            </div>
        @endunless
    </div>
</section>
