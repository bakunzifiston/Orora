@extends('layouts.admin')

@section('title', __('Animal listing details'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-ecommerce">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ $listing->title }}</h1>
                <p class="admin-dash__period">{{ $listing->listing_code }} · {{ __($statuses[$listing->status] ?? ucfirst($listing->status)) }}</p>
            </div>
            <div class="admin-ecommerce__row-actions">
                <a href="{{ route('central.ecommerce.listings.edit', $listing) }}" class="dash-btn-save">{{ __('Edit listing') }}</a>
                <a href="{{ route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Back to listings') }}</a>
            </div>
        </div>

        <div class="admin-ecommerce__detail-grid">
            <article class="farm-panel">
                <h2 class="farm-panel__title">{{ __('Animal details') }}</h2>
                <dl class="admin-ecommerce__details">
                    <dt>{{ __('Species') }}</dt><dd>{{ $listing->animal_species ? __(ucfirst($listing->animal_species)) : '—' }}</dd>
                    <dt>{{ __('Breed') }}</dt><dd>{{ $listing->breed ?: '—' }}</dd>
                    <dt>{{ __('Age') }}</dt><dd>{{ $listing->age ?: '—' }}</dd>
                    <dt>{{ __('Sex') }}</dt><dd>{{ $listing->animal_sex ? __(ucfirst($listing->animal_sex)) : '—' }}</dd>
                    <dt>{{ __('Weight (kg)') }}</dt><dd>{{ $listing->weight_kg ?: '—' }}</dd>
                    <dt>{{ __('Animal record') }}</dt><dd>{{ $listing->animal?->tag_number ?? __('Manual listing') }}</dd>
                    <dt>{{ __('Quantity available') }}</dt><dd>{{ $listing->quantityLabel() ?: '—' }}</dd>
                    <dt>{{ __('Asking price (RWF)') }}</dt><dd>{{ number_format((float) $listing->price) }} RWF</dd>
                    <dt>{{ __('Description') }}</dt><dd>{{ $listing->description }}</dd>
                </dl>
                @if ($listing->images)
                    <div class="admin-ecommerce__photos">
                        @foreach ($listing->images as $image)
                            <img src="{{ $listing->imageUrl($image) }}" alt="{{ $listing->title }}">
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="farm-panel">
                <h2 class="farm-panel__title">{{ __('Farmer information') }}</h2>
                <dl class="admin-ecommerce__details">
                    <dt>{{ __('Farmer') }}</dt><dd>{{ $listing->seller_name }}</dd>
                    <dt>{{ __('Phone') }}</dt><dd><a href="tel:{{ $listing->seller_phone }}">{{ $listing->seller_phone }}</a></dd>
                    <dt>{{ __('Email') }}</dt><dd>{{ $listing->seller_email ?: '—' }}</dd>
                    <dt>{{ __('Farm') }}</dt><dd>{{ $listing->farm?->name ?? '—' }}</dd>
                    <dt>{{ __('Workspace') }}</dt><dd>{{ $listing->tenant?->name ?? $listing->tenant_id }}</dd>
                    <dt>{{ __('Location') }}</dt><dd>{{ $listing->locationLabel() }}</dd>
                    <dt>{{ __('Submitted') }}</dt><dd>{{ $listing->created_at->format('M j, Y H:i') }}</dd>
                </dl>
            </article>
        </div>

        <section class="farm-dash__section">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div><h2 class="farm-panel__title">{{ __('Manage listing status') }}</h2></div>
                </header>
                <form method="POST" action="{{ route('central.ecommerce.listings.status', $listing) }}" class="admin-ecommerce__status-form">
                    @csrf
                    @method('PATCH')
                    <label>{{ __('Status') }}
                        <select name="status">
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected($listing->status === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="admin-ecommerce__status-note">{{ __('Status note') }}
                        <input name="note" maxlength="1000" placeholder="{{ __('Optional reason or buyer/farmer follow-up note') }}">
                    </label>
                    <button type="submit" class="dash-btn-save">{{ __('Update status') }}</button>
                </form>
            </article>
        </section>

        <div class="admin-ecommerce__detail-grid">
            <article class="farm-panel">
                <h2 class="farm-panel__title">{{ __('Buyer inquiries') }} ({{ $listing->inquiries->count() }})</h2>
                @forelse ($listing->inquiries as $inquiry)
                    <div class="admin-ecommerce__inquiry-card">
                        <strong>{{ $inquiry->buyer_name }}</strong>
                        <span>{{ $inquiry->buyer_phone }}@if($inquiry->buyer_email) · {{ $inquiry->buyer_email }}@endif</span>
                        @if ($inquiry->buyer_location)<span>{{ $inquiry->buyer_location }}</span>@endif
                        <p>{{ $inquiry->message }}</p>
                        <form method="POST" action="{{ route('central.ecommerce.inquiries.update', $inquiry) }}" class="admin-ecommerce__inquiry-action">
                            @csrf
                            @method('PATCH')
                            <label>{{ __('Inquiry status') }}
                                <select name="status">
                                    @foreach (['new', 'contacted', 'closed'] as $status)
                                        <option value="{{ $status }}" @selected($inquiry->status === $status)>{{ __(ucfirst($status)) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button class="dash-btn-cancel" type="submit">{{ __('Update') }}</button>
                        </form>
                    </div>
                @empty
                    <p class="dash-empty">{{ __('No buyer inquiries yet.') }}</p>
                @endforelse
            </article>

            <article class="farm-panel">
                <h2 class="farm-panel__title">{{ __('Status history') }}</h2>
                @forelse ($listing->statusChanges as $change)
                    <div class="admin-ecommerce__history-item">
                        <strong>{{ $change->from_status ? __($statuses[$change->from_status] ?? ucfirst($change->from_status)).' → ' : '' }}{{ __($statuses[$change->to_status] ?? ucfirst($change->to_status)) }}</strong>
                        <span>{{ $change->created_at->format('M j, Y H:i') }} · {{ $change->admin?->name ?? $change->user?->name ?? __('System') }}</span>
                        @if ($change->note)<p>{{ $change->note }}</p>@endif
                    </div>
                @empty
                    <p class="dash-empty">{{ __('No status changes recorded.') }}</p>
                @endforelse
            </article>
        </div>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-ecommerce__detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .admin-ecommerce__details { display: grid; grid-template-columns: minmax(8rem, 0.8fr) minmax(0, 1.2fr); gap: 0.65rem 1rem; margin: 1rem 0 0; }
        .admin-ecommerce__details dt { color: #64748b; font-size: 0.85rem; }
        .admin-ecommerce__details dd { margin: 0; overflow-wrap: anywhere; }
        .admin-ecommerce__photos { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1rem; }
        .admin-ecommerce__photos img { width: 7rem; height: 7rem; object-fit: cover; border-radius: 0.5rem; }
        .admin-ecommerce__status-form { display: flex; flex-wrap: wrap; align-items: end; gap: 0.75rem; }
        .admin-ecommerce__status-form label, .admin-ecommerce__inquiry-action label { display: grid; gap: 0.3rem; color: #475569; font-size: 0.8rem; }
        .admin-ecommerce__status-form select, .admin-ecommerce__status-form input, .admin-ecommerce__inquiry-action select { padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 0.4rem; }
        .admin-ecommerce__status-note { flex: 1; min-width: 14rem; }
        .admin-ecommerce__inquiry-card, .admin-ecommerce__history-item { display: grid; gap: 0.35rem; padding: 0.8rem 0; border-bottom: 1px solid #e5e7eb; }
        .admin-ecommerce__inquiry-card span, .admin-ecommerce__history-item span { color: #64748b; font-size: 0.82rem; }
        .admin-ecommerce__inquiry-card p, .admin-ecommerce__history-item p { margin: 0.25rem 0; white-space: pre-wrap; }
        @media (max-width: 800px) { .admin-ecommerce__detail-grid { grid-template-columns: 1fr; } }
    </style>
@endpush
