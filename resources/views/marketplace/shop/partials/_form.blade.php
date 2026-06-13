@php
    $listing = $listing ?? null;
    $isEdit = isset($listing);
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('marketplace.shop.update', $listing) : route('marketplace.shop.store') }}"
    enctype="multipart/form-data"
    class="shop-form"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset class="shop-form__section">
        <legend>Listing details</legend>

        <div class="shop-form__group">
            <span class="shop-form__label">Category *</span>
            <div class="shop-form__radios">
                @foreach ($categories as $category)
                    @php
                        $type = config('marketplace.shop.category_type_map.'.$category->slug, 'animal');
                    @endphp
                    <label class="shop-form__radio">
                        <input
                            type="radio"
                            name="category_id"
                            value="{{ $category->id }}"
                            data-listing-type="{{ $type }}"
                            @checked(old('category_id', $listing?->category_id) == $category->id)
                            required
                        >
                        {{ $category->icon }} {{ $category->name }}
                    </label>
                @endforeach
            </div>
            <input type="hidden" name="listing_type" id="listing_type" value="{{ old('listing_type', $listing?->listing_type ?? 'animal') }}">
            @error('category_id')<p class="mp-field-error">{{ $message }}</p>@enderror
            @error('listing_type')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="mp-field mp-field--full">
            <label for="title">Title *</label>
            <input type="text" id="title" name="title" value="{{ old('title', $listing?->title) }}" required>
            @error('title')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="mp-field mp-field--full">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="5" required>{{ old('description', $listing?->description) }}</textarea>
            @error('description')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>

        <div class="shop-form__row">
            <div class="mp-field">
                <label for="breed">Breed</label>
                <input type="text" id="breed" name="breed" value="{{ old('breed', $listing?->breed) }}">
            </div>
            <div class="mp-field">
                <label for="age">Age</label>
                <input type="text" id="age" name="age" value="{{ old('age', $listing?->age) }}" placeholder="e.g. 2 years">
            </div>
            <div class="mp-field">
                <label for="weight_kg">Weight (kg)</label>
                <input type="number" step="0.01" id="weight_kg" name="weight_kg" value="{{ old('weight_kg', $listing?->weight_kg) }}">
            </div>
        </div>

        <div class="shop-form__row">
            <div class="mp-field">
                <label for="quantity">Quantity *</label>
                <input type="number" step="0.001" id="quantity" name="quantity" value="{{ old('quantity', $listing?->quantity) }}" required>
                @error('quantity')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="unit">Unit *</label>
                <select id="unit" name="unit" required>
                    @foreach ($units as $value => $label)
                        <option value="{{ $value }}" @selected(old('unit', $listing?->unit) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('unit')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="price">Price (RWF) *</label>
                <input type="number" step="1" id="price" name="price" value="{{ old('price', $listing?->price) }}" required>
                @error('price')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="price_type">Price type *</label>
                <select id="price_type" name="price_type" required>
                    @foreach ($priceTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('price_type', $listing?->price_type ?? 'fixed') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('price_type')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="shop-form__section">
        <legend>Seller details</legend>
        <div class="shop-form__row">
            <div class="mp-field">
                <label for="seller_name">Seller name *</label>
                <input type="text" id="seller_name" name="seller_name" value="{{ old('seller_name', $listing?->seller_name ?? $defaultSeller['seller_name'] ?? '') }}" required>
                @error('seller_name')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="seller_phone">Phone *</label>
                <input type="text" id="seller_phone" name="seller_phone" value="{{ old('seller_phone', $listing?->seller_phone) }}" required>
                @error('seller_phone')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="seller_email">Email</label>
                <input type="email" id="seller_email" name="seller_email" value="{{ old('seller_email', $listing?->seller_email ?? $defaultSeller['seller_email'] ?? '') }}">
            </div>
            <div class="mp-field">
                <label for="seller_type">Seller type *</label>
                <select id="seller_type" name="seller_type" required>
                    @foreach ($sellerTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('seller_type', $listing?->seller_type ?? 'individual') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('seller_type')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </fieldset>

    <fieldset class="shop-form__section">
        <legend>Location</legend>
        <div class="shop-form__row">
            <div class="mp-field">
                <label for="location_district">District *</label>
                <select id="location_district" name="location_district" required>
                    <option value="">Select district</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district }}" @selected(old('location_district', $listing?->location_district) === $district)>{{ $district }}</option>
                    @endforeach
                </select>
                @error('location_district')<p class="mp-field-error">{{ $message }}</p>@enderror
            </div>
            <div class="mp-field">
                <label for="location_sector">Sector</label>
                <input type="text" id="location_sector" name="location_sector" value="{{ old('location_sector', $listing?->location_sector) }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="shop-form__section">
        <legend>Images</legend>
        <p class="shop-form__hint">Upload up to 5 photos (JPG, PNG — max 4MB each)</p>

        @if ($isEdit && ($listing->images ?? []))
            <div class="shop-form__existing">
                @foreach ($listing->images as $image)
                    <label class="shop-form__existing-item">
                        <img src="{{ asset($image) }}" alt="">
                        <input type="checkbox" name="keep_images[]" value="{{ $image }}" checked> Keep
                    </label>
                @endforeach
            </div>
        @endif

        <div class="mp-field mp-field--full">
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/jpg" multiple>
            @error('images.*')<p class="mp-field-error">{{ $message }}</p>@enderror
        </div>
    </fieldset>

    <div class="shop-form__actions">
        <a href="{{ $isEdit ? route('marketplace.shop.show', $listing) : route('marketplace.shop') }}" class="lp-btn lp-btn--outline">Cancel</a>
        <button type="submit" class="lp-btn lp-btn--primary">{{ $isEdit ? 'Save Changes' : 'Post Listing' }}</button>
    </div>
</form>

<script>
    document.querySelectorAll('[name="category_id"]').forEach((input) => {
        input.addEventListener('change', () => {
            if (input.checked) {
                document.getElementById('listing_type').value = input.dataset.listingType || 'animal';
            }
        });
    });
</script>
