@props([
    'filters' => [],
    'districts' => [],
    'sellerTypes' => [],
    'sortOptions' => [],
])

<aside class="shop-filters" id="shop-filters" data-shop-filters>
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
            <h3 class="shop-filters__title">Filters</h3>
            <button type="button" class="shop-filters__close" data-shop-filters-close aria-label="Close filters">×</button>
        </div>

        <div class="shop-filters__group">
            <label for="district">Location</label>
            <div class="shop-select">
                <select name="district" id="district">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="shop-filters__group">
            <span class="shop-filters__label">Price (RWF)</span>
            <div class="shop-filters__range">
                <input type="number" name="price_min" placeholder="Min" value="{{ $filters['price_min'] ?? '' }}" min="0" inputmode="numeric">
                <span class="shop-filters__range-sep" aria-hidden="true"></span>
                <input type="number" name="price_max" placeholder="Max" value="{{ $filters['price_max'] ?? '' }}" min="0" inputmode="numeric">
            </div>
        </div>

        <div class="shop-filters__group">
            <span class="shop-filters__label">Seller type</span>
            <div class="shop-filters__checks">
                @foreach ($sellerTypes as $value => $label)
                    <label class="shop-filters__check">
                        <input
                            type="checkbox"
                            name="seller_type[]"
                            value="{{ $value }}"
                            @checked(in_array($value, (array) ($filters['seller_type'] ?? []), true))
                        >
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="shop-filters__group">
            <label class="shop-filters__check shop-filters__check--single">
                <input type="checkbox" name="verified" value="1" @checked($filters['verified'] ?? false)>
                <span>Verified sellers only</span>
            </label>
        </div>

        <div class="shop-filters__actions">
            <button type="submit" class="lp-btn lp-btn--primary lp-btn--block">Apply filters</button>
            <a href="{{ route('marketplace.shop') }}" class="lp-btn lp-btn--outline lp-btn--block">Reset</a>
        </div>
    </form>
</aside>
