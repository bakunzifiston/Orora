@extends('layouts.admin')

@section('title', $listing ? __('Edit animal listing') : __('Create animal listing'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-ecommerce">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar admin-ecommerce__head">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ $listing ? __('Edit animal listing') : __('Create animal listing') }}</h1>
                <p class="admin-dash__period">{{ __('Fill in the five sections below. Linking a farm animal record fills in its details for you.') }}</p>
            </div>
            <a href="{{ route('central.ecommerce.index') }}" class="dash-btn-cancel">← {{ __('Back to listings') }}</a>
        </div>

        <form
            method="POST"
            action="{{ $listing ? route('central.ecommerce.listings.update', $listing) : route('central.ecommerce.listings.store') }}"
            enctype="multipart/form-data"
            class="admin-ecommerce__form"
        >
            @csrf
            @if ($listing) @method('PUT') @endif

            <section class="farm-panel admin-ecommerce__section">
                <header class="admin-ecommerce__section-head">
                    <span class="admin-ecommerce__step">1</span>
                    <div>
                        <h2>{{ __('Farm and animal') }}</h2>
                        <p>{{ __('Which farm is selling, and which animal record this listing is for.') }}</p>
                    </div>
                </header>
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
                    <span class="admin-ecommerce__hint">{{ __('Picking an animal fills in its farm. Leave as manual if the animal is not recorded in OroraFarm.') }}</span>
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
                    <input name="title" value="{{ old('title', $listing?->title) }}" required maxlength="255" placeholder="{{ __('For example, Friesian heifer, 2 years') }}">
                    @error('title')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                </div>
            </section>

            <section class="farm-panel admin-ecommerce__section">
                <header class="admin-ecommerce__section-head">
                    <span class="admin-ecommerce__step">2</span>
                    <div>
                        <h2>{{ __('Animal details') }}</h2>
                        <p>{{ __('Shown to buyers on the listing page.') }}</p>
                    </div>
                </header>
                <div class="admin-ecommerce__form-grid admin-ecommerce__form-grid--3">
                <label>{{ __('Species') }}
                    <input name="animal_species" value="{{ old('animal_species', $listing?->animal_species ?: $listing?->animal?->species) }}" maxlength="80" placeholder="{{ __('For example, cattle') }}">
                </label>
                <label>{{ __('Sex') }}
                    @php($sexValue = old('animal_sex', $listing?->animal_sex ?: $listing?->animal?->gender))
                    <select name="animal_sex">
                        <option value="">{{ __('Not specified') }}</option>
                        @foreach (config('modules.animal_genders', []) as $value => $label)
                            <option value="{{ $value }}" @selected(strtolower((string) $sexValue) === $value)>{{ __($label) }}</option>
                        @endforeach
                        @if (filled($sexValue) && ! array_key_exists(strtolower((string) $sexValue), config('modules.animal_genders', [])))
                            <option value="{{ $sexValue }}" selected>{{ $sexValue }}</option>
                        @endif
                    </select>
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
                </div>
            </section>

            <section class="farm-panel admin-ecommerce__section">
                <header class="admin-ecommerce__section-head">
                    <span class="admin-ecommerce__step">3</span>
                    <div>
                        <h2>{{ __('Price and quantity') }}</h2>
                        <p>{{ __('How many are for sale and the asking price.') }}</p>
                    </div>
                </header>
                <div class="admin-ecommerce__form-grid admin-ecommerce__form-grid--4">
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
                </div>
            </section>

            <section class="farm-panel admin-ecommerce__section">
                <header class="admin-ecommerce__section-head">
                    <span class="admin-ecommerce__step">4</span>
                    <div>
                        <h2>{{ __('Seller and location') }}</h2>
                        <p>{{ __('How buyers reach the farmer and where the animal is.') }}</p>
                    </div>
                </header>
                <div class="admin-ecommerce__form-grid admin-ecommerce__form-grid--3">
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
                </div>
            </section>

            <section class="farm-panel admin-ecommerce__section">
                <header class="admin-ecommerce__section-head">
                    <span class="admin-ecommerce__step">5</span>
                    <div>
                        <h2>{{ __('Description and photos') }}</h2>
                        <p>{{ __('Listings with clear photos and details get more buyer inquiries.') }}</p>
                    </div>
                </header>
                <div class="admin-ecommerce__form-grid">
                <label class="admin-ecommerce__full">{{ __('Description') }} *
                    <textarea name="description" rows="5" required maxlength="2000" placeholder="{{ __('Health, vaccinations, temperament, feeding and anything a buyer should know.') }}">{{ old('description', $listing?->description) }}</textarea>
                    @error('description')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </label>
                <div class="admin-ecommerce__full">
                    <span class="admin-ecommerce__label">{{ __('Photos') }}</span>
                    <label class="admin-ecommerce__dropzone">
                        <input type="file" name="images[]" accept="image/jpeg,image/png" multiple data-ecommerce-photos>
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/>
                            <circle cx="8.5" cy="10" r="1.5" fill="currentColor"/>
                            <path d="M21 16l-5.5-5.5L7 19" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <strong data-ecommerce-photos-label>{{ __('Click to add photos') }}</strong>
                        <small>{{ __('Up to 5 JPG or PNG images, 4 MB each') }}</small>
                    </label>
                    @if ($listing && $listing->images)
                        <span class="admin-ecommerce__existing-images">
                            @foreach ($listing->images as $image)
                                <span><img src="{{ $listing->imageUrl($image) }}" alt=""><label><input type="checkbox" name="keep_images[]" value="{{ $image }}" checked> {{ __('Keep') }}</label></span>
                            @endforeach
                        </span>
                    @endif
                    @error('images.*')<span class="admin-ecommerce__error">{{ $message }}</span>@enderror
                </div>
                </div>
            </section>

            <div class="farm-panel admin-ecommerce__form-actions">
                <p>{{ $listing ? __('Changes are saved to the listing straight away.') : __('The listing is saved as a draft. Approve and publish it from the listing page.') }}</p>
                <div>
                    <a href="{{ $listing ? route('central.ecommerce.listings.show', $listing) : route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                    <button type="submit" class="dash-btn-save">{{ $listing ? __('Save changes') : __('Create draft listing') }}</button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-ecommerce__head { flex-direction: row !important; align-items: center !important; justify-content: space-between; flex-wrap: wrap; }
        .admin-ecommerce__head .admin-dash__header-text { flex: 1 1 18rem !important; }
        .admin-ecommerce__head .admin-dash__period { white-space: normal; overflow: visible; text-overflow: clip; }
        .admin-ecommerce__form { display: grid; gap: 1rem; margin-top: 1rem; }
        .admin-ecommerce__section { padding: 1.35rem 1.5rem 1.5rem; }
        .admin-ecommerce__section-head { display: flex; align-items: flex-start; gap: 0.85rem; margin-bottom: 1.15rem; padding-bottom: 1rem; border-bottom: 1px solid #e2e8f0; }
        .admin-ecommerce__section-head h2 { margin: 0; color: #0f172a; font-size: 1rem; font-weight: 700; }
        .admin-ecommerce__section-head p { margin: 0.15rem 0 0; color: #64748b; font-size: 0.8125rem; }
        .admin-ecommerce__step {
            display: grid; place-items: center; flex-shrink: 0; width: 1.9rem; height: 1.9rem;
            border-radius: 999px; background: #002b2b; color: #a4d400; font-size: 0.8125rem; font-weight: 800;
        }
        .admin-ecommerce__form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem 1.15rem; }
        .admin-ecommerce__form-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .admin-ecommerce__form-grid--4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .admin-ecommerce__form label, .admin-ecommerce__label { display: grid; align-content: start; gap: 0.35rem; color: #334155; font-size: 0.8125rem; font-weight: 600; }
        .admin-ecommerce__form input:not([type="checkbox"]):not([type="file"]), .admin-ecommerce__form select, .admin-ecommerce__form textarea {
            width: 100%; min-width: 0; height: 2.6rem; padding: 0 0.75rem;
            border: 1px solid #cbd5e1; border-radius: 0.55rem; background: #fff; color: #0f172a; font: inherit; font-size: 0.875rem; font-weight: 400;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .admin-ecommerce__form textarea { height: auto; min-height: 7rem; padding: 0.65rem 0.75rem; resize: vertical; }
        .admin-ecommerce__form input:focus, .admin-ecommerce__form select:focus, .admin-ecommerce__form textarea:focus {
            outline: none; border-color: #002b2b; box-shadow: 0 0 0 3px rgba(164, 212, 0, 0.28);
        }
        .admin-ecommerce__full { grid-column: 1 / -1; display: grid; gap: 0.35rem; }
        .admin-ecommerce__hint { color: #64748b; font-size: 0.75rem; font-weight: 400; }
        .admin-ecommerce__error { color: #b91c1c; font-size: 0.8rem; font-weight: 500; }
        .admin-ecommerce__dropzone {
            position: relative; display: grid !important; justify-items: center; gap: 0.3rem !important;
            padding: 1.5rem 1rem; border: 1.5px dashed #cbd5e1; border-radius: 0.75rem; background: #f8fafc;
            color: #64748b !important; text-align: center; cursor: pointer; transition: border-color 0.15s, background 0.15s;
        }
        .admin-ecommerce__dropzone:hover, .admin-ecommerce__dropzone:focus-within { border-color: #002b2b; background: #f4f9e6; }
        .admin-ecommerce__dropzone input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .admin-ecommerce__dropzone strong { color: #0f3d3d; font-size: 0.875rem; }
        .admin-ecommerce__dropzone small { font-size: 0.75rem; font-weight: 400; }
        .admin-ecommerce__form .admin-ecommerce__form-actions {
            flex-direction: row; text-align: left;
            position: sticky; bottom: 0.75rem; z-index: 5; display: flex; align-items: center; justify-content: space-between;
            flex-wrap: wrap; gap: 0.75rem; padding: 0.85rem 1.25rem; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
        }
        .admin-ecommerce__form-actions p { margin: 0; color: #64748b; font-size: 0.8125rem; }
        .admin-ecommerce__form-actions > div { display: flex; gap: 0.6rem; }
        .admin-ecommerce__existing-images { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 0.5rem; }
        .admin-ecommerce__existing-images > span { display: grid; gap: 0.3rem; }
        .admin-ecommerce__existing-images img { width: 5rem; height: 5rem; object-fit: cover; border-radius: 0.5rem; }
        @media (max-width: 1100px) {
            .admin-ecommerce__form-grid--3, .admin-ecommerce__form-grid--4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .admin-ecommerce__form-grid, .admin-ecommerce__form-grid--3, .admin-ecommerce__form-grid--4 { grid-template-columns: 1fr; }
            .admin-ecommerce__section { padding: 1.1rem; }
            .admin-ecommerce__form-actions > div { width: 100%; }
            .admin-ecommerce__form-actions > div > * { flex: 1; justify-content: center; text-align: center; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const photos = document.querySelector('[data-ecommerce-photos]');
            const photosLabel = document.querySelector('[data-ecommerce-photos-label]');
            photos?.addEventListener('change', () => {
                const count = photos.files.length;
                photosLabel.textContent = count ? `${count} photo${count === 1 ? '' : 's'} selected` : @json(__('Click to add photos'));
            });

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
