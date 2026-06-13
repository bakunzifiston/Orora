@props([
    'filters' => [],
    'districts' => [],
    'sellerTypes' => [],
    'sortOptions' => [],
])

<aside class="shop-filters" id="shop-filters">
    <form method="GET" action="{{ route('marketplace.shop') }}" class="shop-filters__form">
        @if ($filters['category'] ?? false)
            <input type="hidden" name="category" value="{{ $filters['category'] }}">
        @endif
        @if ($filters['q'] ?? false)
            <input type="hidden" name="q" value="{{ $filters['q'] }}">
        @endif

        <h3 class="shop-filters__title">Filters</h3>

        <div class="shop-filters__group">
            <label for="district">Location</label>
            <select name="district" id="district">
                <option value="">All districts</option>
                @foreach ($districts as $district)
                    <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                @endforeach
            </select>
        </div>

        <div class="shop-filters__group">
            <label>Price range (RWF)</label>
            <div class="shop-filters__range">
                <input type="number" name="price_min" placeholder="Min" value="{{ $filters['price_min'] ?? '' }}" min="0">
                <span>—</span>
                <input type="number" name="price_max" placeholder="Max" value="{{ $filters['price_max'] ?? '' }}" min="0">
            </div>
        </div>

        <div class="shop-filters__group">
            <span class="shop-filters__label">Seller type</span>
            @foreach ($sellerTypes as $value => $label)
                <label class="shop-filters__check">
                    <input
                        type="checkbox"
                        name="seller_type[]"
                        value="{{ $value }}"
                        @checked(in_array($value, (array) ($filters['seller_type'] ?? []), true))
                    >
                    {{ $label }}
                </label>
            @endforeach
        </div>

        <div class="shop-filters__group">
            <label class="shop-filters__check">
                <input type="checkbox" name="verified" value="1" @checked($filters['verified'] ?? false)>
                Verified only
            </label>
        </div>

        <div class="shop-filters__actions">
            <button type="submit" class="lp-btn lp-btn--primary lp-btn--block">Apply</button>
            <a href="{{ route('marketplace.shop') }}" class="lp-btn lp-btn--outline lp-btn--block">Reset</a>
        </div>
    </form>
</aside>
