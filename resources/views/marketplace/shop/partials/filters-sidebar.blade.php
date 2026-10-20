@props([
    'filters' => [],
    'districts' => [],
    'sellerTypes' => [],
    'sortOptions' => [],
])

@php
    $districtOptions = collect($districts)->filter()->unique()->sort()->values();
    $selectedDistrict = $filters['district'] ?? '';
    $selectedSellerTypes = (array) ($filters['seller_type'] ?? []);
@endphp

<aside class="shop-filters" id="shop-filters" data-shop-filters aria-label="Filters">
    <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-filters__form" data-shop-form>
        @if ($filters['category'] ?? false)
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
        @endif
        @if ($filters['q'] ?? false)
            <input type="hidden" name="q" value="{{ $filters['q'] }}">
        @endif
        @if ($filters['sort'] ?? false)
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
        @endif

        <div class="shop-filters__head">
            <h2 class="shop-filters__title">Filters</h2>
            <button type="button" class="shop-filters__close" data-shop-filters-close aria-label="Close filters">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <div class="shop-filters__group">
            <label for="district" class="shop-filters__label">Location</label>
            <div class="shop-select" data-shop-combobox>
                <select name="district" id="district">
                    <option value="">All districts</option>
                    @foreach ($districtOptions as $district)
                        <option value="{{ $district }}" @selected($selectedDistrict === $district)>{{ $district }}</option>
                    @endforeach
                    @if ($selectedDistrict !== '' && ! $districtOptions->contains($selectedDistrict))
                        <option value="{{ $selectedDistrict }}" selected>{{ $selectedDistrict }}</option>
                    @endif
                </select>
            </div>
        </div>

        <fieldset class="shop-filters__group">
            <legend class="shop-filters__label">Price <span class="shop-filters__unit">RWF</span></legend>
            <div class="shop-filters__range">
                <label class="shop-filters__price">
                    <span>Min</span>
                    <input type="number" name="price_min" placeholder="0" value="{{ $filters['price_min'] ?? '' }}" min="0" step="1" inputmode="numeric">
                </label>
                <label class="shop-filters__price">
                    <span>Max</span>
                    <input type="number" name="price_max" placeholder="Any" value="{{ $filters['price_max'] ?? '' }}" min="0" step="1" inputmode="numeric">
                </label>
            </div>
        </fieldset>

        <fieldset class="shop-filters__group">
            <legend class="shop-filters__label">Seller type</legend>
            <div class="shop-filters__chips">
                @foreach ($sellerTypes as $value => $label)
                    <label class="shop-filters__chip">
                        <input
                            type="checkbox"
                            name="seller_type[]"
                            value="{{ $value }}"
                            @checked(in_array($value, $selectedSellerTypes, true))
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="shop-filters__group">
            <label class="shop-filters__toggle">
                <span class="shop-filters__toggle-text">
                    <strong>Verified sellers only</strong>
                    <small>Sellers checked by Orora</small>
                </span>
                <input type="checkbox" name="verified" value="1" role="switch" @checked($filters['verified'] ?? false)>
                <span class="shop-filters__switch" aria-hidden="true"></span>
            </label>
        </div>

        <div class="shop-filters__actions">
            <button type="submit" class="lp-btn lp-btn--primary lp-btn--block">Apply filters</button>
            <a href="{{ route('marketplace.shop') }}" class="shop-filters__reset">Reset filters</a>
        </div>
    </form>
</aside>
