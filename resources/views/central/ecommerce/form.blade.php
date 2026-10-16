@extends('layouts.admin')

@section('title', $listing ? __('Edit animal listing') : __('Create animal listing'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-ecommerce">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ $listing ? __('Edit animal listing') : __('Create animal listing') }}</h1>
                <p class="admin-dash__period">{{ __('Use a farm animal record where available; manual listings remain supported.') }}</p>
            </div>
            <a href="{{ route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Back to listings') }}</a>
        </div>

        <form
            method="POST"
            action="{{ $listing ? route('central.ecommerce.listings.update', $listing) : route('central.ecommerce.listings.store') }}"
            enctype="multipart/form-data"
            class="farm-panel admin-ecommerce__form"
        >
            @csrf
            @if ($listing) @method('PUT') @endif

            <div class="admin-ecommerce__form-grid">
                <label>{{ __('Farm') }} *
                    <select name="farm_id" required data-marketplace-farm>
                        <option value="">{{ __('Select farm') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected((string) old('farm_id', $listing?->farm_id) === (string) $farm->id)>
                                {{ $farm->name }} — {{ $farm->owner_full_name ?: $farm->tenant_id }}
                            </option>
                        @endforeach
                    </select>
                    @error('farm_id')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Link existing animal') }}
                    <select name="animal_id" data-marketplace-animal>
                        <option value="">{{ __('Manual listing — no animal record') }}</option>
                        @foreach ($animals as $animal)
                            <option value="{{ $animal->id }}" data-farm-id="{{ $animal->farm_id }}" @selected((string) old('animal_id', $listing?->animal_id) === (string) $animal->id)>
                                {{ $animal->tag_number }} — {{ $animal->name ?: $animal->species }} ({{ $animal->farm?->name }})
                            </option>
                        @endforeach
                    </select>
                    @error('animal_id')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Category') }} *
                    <select name="category_id" required>
                        <option value="">{{ __('Select category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $listing?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Title') }} *
                    <input name="title" value="{{ old('title', $listing?->title) }}" required maxlength="255">
                    @error('title')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>

                <label>{{ __('Species') }}
                    <input name="animal_species" value="{{ old('animal_species', $listing?->animal_species ?: $listing?->animal?->species) }}" maxlength="80">
                </label>
                <label>{{ __('Sex') }}
                    <input name="animal_sex" value="{{ old('animal_sex', $listing?->animal_sex ?: $listing?->animal?->gender) }}" maxlength="40">
                </label>
                <label>{{ __('Breed') }}
                    <input name="breed" value="{{ old('breed', $listing?->breed ?: $listing?->animal?->breed) }}" maxlength="120">
                </label>
                <label>{{ __('Age') }}
                    <input name="age" value="{{ old('age', $listing?->age ?: $listing?->animal?->age_label) }}" placeholder="{{ __('For example, 2 years') }}" maxlength="60">
                </label>
                <label>{{ __('Weight (kg)') }}
                    <input type="number" name="weight_kg" min="0" step="0.01" value="{{ old('weight_kg', $listing?->weight_kg ?: $listing?->animal?->weight_kg) }}">
                    @error('weight_kg')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Quantity available') }} *
                    <input type="number" name="quantity" min="0.001" step="0.001" value="{{ old('quantity', $listing?->quantity ?? 1) }}" required>
                    @error('quantity')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Unit') }} *
                    <select name="unit" required>
                        @foreach ($units as $unit => $label)
                            <option value="{{ $unit }}" @selected(old('unit', $listing?->unit ?? 'head') === $unit)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('Asking price (RWF)') }} *
                    <input type="number" name="price" min="0" step="1" value="{{ old('price', $listing?->price) }}" required>
                    @error('price')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Price type') }} *
                    <select name="price_type" required>
                        @foreach ($priceTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('price_type', $listing?->price_type ?? 'fixed') === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('Seller / farmer') }} *
                    <input name="seller_name" value="{{ old('seller_name', $listing?->seller_name ?: $listing?->farm?->owner_full_name) }}" required maxlength="120">
                    @error('seller_name')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Phone') }} *
                    <input name="seller_phone" value="{{ old('seller_phone', $listing?->seller_phone ?: $listing?->farm?->contact_phone) }}" required maxlength="40">
                    @error('seller_phone')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Email') }}
                    <input type="email" name="seller_email" value="{{ old('seller_email', $listing?->seller_email ?: $listing?->farm?->contact_email) }}" maxlength="255">
                    @error('seller_email')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Seller type') }} *
                    <select name="seller_type" required>
                        @foreach ($sellerTypes as $key => $label)
                            <option value="{{ $key }}" @selected(old('seller_type', $listing?->seller_type ?? 'individual') === $key)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </label>
                <label>{{ __('District') }} *
                    <select name="location_district" required>
                        <option value="">{{ __('Select district') }}</option>
                        @foreach ($districts as $district)
                            <option value="{{ $district }}" @selected(old('location_district', $listing?->location_district ?: $listing?->farm?->district) === $district)>{{ $district }}</option>
                        @endforeach
                    </select>
                    @error('location_district')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label>{{ __('Sector') }}
                    <input name="location_sector" value="{{ old('location_sector', $listing?->location_sector ?: $listing?->farm?->sector) }}" maxlength="120">
                </label>
                <label class="admin-ecommerce__description">{{ __('Description') }} *
                    <textarea name="description" rows="5" required maxlength="2000">{{ old('description', $listing?->description) }}</textarea>
                    @error('description')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <label class="admin-ecommerce__description">{{ __('Animal photographs') }}
                    <input type="file" name="images[]" accept="image/jpeg,image/png" multiple>
                    @if ($listing && $listing->images)
                        <span class="admin-ecommerce__existing-images">
                            @foreach ($listing->images as $image)
                                <span><img src="{{ $listing->imageUrl($image) }}" alt=""><label><input type="checkbox" name="keep_images[]" value="{{ $image }}" checked> {{ __('Keep') }}</label></span>
                            @endforeach
                        </span>
                    @endif
                    @error('images.*')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
            </div>

            <div class="admin-ecommerce__form-actions">
                <button type="submit" class="dash-btn-save">{{ $listing ? __('Save changes') : __('Create draft listing') }}</button>
                <a href="{{ $listing ? route('central.ecommerce.listings.show', $listing) : route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-ecommerce__form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
        .admin-ecommerce__form label { display: grid; align-content: start; gap: 0.35rem; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .admin-ecommerce__form input:not([type="checkbox"]):not([type="file"]), .admin-ecommerce__form select, .admin-ecommerce__form textarea {
            width: 100%;
            min-width: 0;
            padding: 0.6rem 0.7rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            background: #fff;
            color: #0f172a;
            font: inherit;
            font-weight: 400;
        }
        .admin-ecommerce__description { grid-column: 1 / -1; }
        .admin-ecommerce__error { color: #b91c1c; font-size: 0.8rem; font-weight: 500; }
        .admin-ecommerce__form-actions { display: flex; gap: 0.75rem; margin-top: 1.25rem; }
        .admin-ecommerce__existing-images { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .admin-ecommerce__existing-images > span { display: grid; gap: 0.3rem; }
        .admin-ecommerce__existing-images img { width: 5rem; height: 5rem; object-fit: cover; border-radius: 0.4rem; }
        @media (max-width: 640px) { .admin-ecommerce__form-grid { grid-template-columns: 1fr; } }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const farm = document.querySelector('[data-marketplace-farm]');
            const animal = document.querySelector('[data-marketplace-animal]');
            if (!farm || !animal) return;
            animal.addEventListener('change', () => {
                const farmId = animal.selectedOptions[0]?.dataset.farmId;
                if (farmId) farm.value = farmId;
            });
        });
    </script>
@endpush
