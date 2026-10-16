@extends('layouts.admin')

@section('title', __('Animal marketplace'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-ecommerce">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ __('Animal marketplace') }}</h1>
                <p class="admin-dash__period">{{ __('Review animal listings and connect buyers with farmers.') }}</p>
            </div>
            <a href="{{ route('central.ecommerce.listings.create') }}" class="dash-btn-save">{{ __('Create listing') }}</a>
        </div>

        <section class="farm-dash__section" aria-label="{{ __('Marketplace summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                @foreach ([
                    ['label' => 'Total animal listings', 'value' => $stats['total'], 'icon' => 'box', 'tone' => 'stock'],
                    ['label' => 'Pending review', 'value' => $stats['pending'], 'icon' => 'health', 'tone' => 'receivable'],
                    ['label' => 'Published', 'value' => $stats['published'], 'icon' => 'sale', 'tone' => 'production'],
                    ['label' => 'Sold or inactive', 'value' => $stats['inactive'], 'icon' => 'animal', 'tone' => 'expense'],
                    ['label' => 'Buyer inquiries', 'value' => $stats['inquiries'], 'icon' => 'mail', 'tone' => 'sales'],
                ] as $kpi)
                    <div class="farm-kpi farm-kpi--{{ $kpi['tone'] }}">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => $kpi['icon']])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __($kpi['label']) }}</div>
                            <div class="farm-kpi__value">{{ number_format($kpi['value']) }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Animal listings') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Animal listings') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($listings->total()) }} {{ __('total') }}</p>
                    </div>
                </header>

                <form method="GET" action="{{ route('central.ecommerce.index') }}" class="admin-ecommerce__filters">
                    <label>
                        <span>{{ __('Search') }}</span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Animal, farmer, phone, location') }}">
                    </label>
                    <label>
                        <span>{{ __('Status') }}</span>
                        <select name="status">
                            <option value="">{{ __('All statuses') }}</option>
                            @foreach ($statuses as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>{{ __('Species') }}</span>
                        <select name="species">
                            <option value="">{{ __('All species') }}</option>
                            @foreach ($species as $animalSpecies)
                                <option value="{{ $animalSpecies }}" @selected(($filters['species'] ?? '') === $animalSpecies)>{{ __(ucfirst($animalSpecies)) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        <span>{{ __('District') }}</span>
                        <select name="district">
                            <option value="">{{ __('All districts') }}</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district }}" @selected(($filters['district'] ?? '') === $district)>{{ $district }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="admin-ecommerce__filter-actions">
                        <button type="submit" class="dash-btn-save dash-btn-save--sm">{{ __('Filter') }}</button>
                        <a href="{{ route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                    </div>
                </form>

                @if ($listings->isEmpty())
                    <p class="dash-empty">{{ __('No animal listings match these filters.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table admin-ecommerce__table">
                            <thead>
                                <tr>
                                    <th>{{ __('Animal') }}</th>
                                    <th>{{ __('Farmer / farm') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Submitted') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listings as $listing)
                                    @php
                                        $tone = match ($listing->status) {
                                            'active', 'published', 'approved' => 'ok',
                                            'pending', 'pending_review' => 'warn',
                                            'rejected', 'sold' => 'bad',
                                            default => 'muted',
                                        };
                                        $speciesLabel = $listing->animal_species ?: $listing->animal?->species;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__stack">
                                                <a href="{{ route('central.ecommerce.listings.show', $listing) }}" class="health-table__title health-table__title--link">{{ $listing->title }}</a>
                                                <span class="health-table__meta">
                                                    {{ $speciesLabel ? __(ucfirst($speciesLabel)) : __('Animal') }}
                                                    @if ($listing->breed) · {{ $listing->breed }}@endif
                                                    @if ($listing->animal_sex || $listing->animal?->gender) · {{ __(ucfirst($listing->animal_sex ?: $listing->animal->gender)) }}@endif
                                                    @if ($listing->age) · {{ $listing->age }}@endif
                                                    @if ($listing->animal) · {{ $listing->animal->tag_number }}@endif
                                                </span>
                                                <span class="health-table__meta">{{ $listing->listing_code }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="health-table__stack">
                                                <span class="health-table__value">{{ $listing->seller_name }}</span>
                                                <span class="health-table__meta">{{ $listing->farm?->name ?? $listing->tenant?->name ?? $listing->tenant_id }}</span>
                                                <span class="health-table__meta">{{ $listing->seller_phone }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $listing->locationLabel() }}</td>
                                        <td>
                                            <span class="health-table__value">{{ number_format((float) $listing->price) }} RWF</span>
                                            <span class="health-table__meta">{{ $listing->quantityLabel() }}</span>
                                        </td>
                                        <td><time datetime="{{ $listing->created_at->toIso8601String() }}">{{ $listing->created_at->format('M j, Y') }}</time></td>
                                        <td><span class="health-table__pill health-table__pill--{{ $tone }}">{{ __($statuses[$listing->status] ?? ucfirst(str_replace('_', ' ', $listing->status))) }}</span></td>
                                        <td>
                                            <div class="admin-ecommerce__row-actions">
                                                <a href="{{ route('central.ecommerce.listings.show', $listing) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                                <a href="{{ route('central.ecommerce.listings.edit', $listing) }}" class="dash-btn-cancel">{{ __('Edit') }}</a>
                                                <form method="POST" action="{{ route('central.ecommerce.listings.status', $listing) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="sr-only" for="status-{{ $listing->id }}">{{ __('Set listing status') }}</label>
                                                    <select id="status-{{ $listing->id }}" name="status">
                                                        @foreach ($statuses as $key => $label)
                                                            <option value="{{ $key }}" @selected($listing->status === $key)>{{ __($label) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="dash-btn-cancel">{{ __('Update') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-pagination">{{ $listings->links() }}</div>
                @endif
            </article>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Recent buyer inquiries') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recent buyer inquiries') }}</h2>
                        <p class="farm-panel__desc">{{ __('Contact the buyer and seller to help connect them.') }}</p>
                    </div>
                </header>
                @if ($inquiries->isEmpty())
                    <p class="dash-empty">{{ __('No buyer inquiries yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Buyer') }}</th>
                                    <th>{{ __('Listing / farmer') }}</th>
                                    <th>{{ __('Message') }}</th>
                                    <th>{{ __('Received') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($inquiries as $inquiry)
                                    <tr>
                                        <td>
                                            <div class="health-table__stack">
                                                <span class="health-table__value">{{ $inquiry->buyer_name }}</span>
                                                <span class="health-table__meta">{{ $inquiry->buyer_phone }}</span>
                                                @if ($inquiry->buyer_email)<span class="health-table__meta">{{ $inquiry->buyer_email }}</span>@endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="health-table__stack">
                                                <a href="{{ route('central.ecommerce.listings.show', $inquiry->listing) }}" class="health-table__title health-table__title--link">{{ $inquiry->listing?->title }}</a>
                                                <span class="health-table__meta">{{ $inquiry->listing?->seller_name }} · {{ $inquiry->listing?->seller_phone }}</span>
                                            </div>
                                        </td>
                                        <td class="admin-ecommerce__message">{{ $inquiry->message }}</td>
                                        <td><time datetime="{{ $inquiry->created_at->toIso8601String() }}">{{ $inquiry->created_at->format('M j, Y') }}</time></td>
                                        <td>{{ __(ucfirst($inquiry->status)) }}</td>
                                        <td>
                                            <form method="POST" action="{{ route('central.ecommerce.inquiries.update', $inquiry) }}" class="admin-ecommerce__inquiry-action">
                                                @csrf
                                                @method('PATCH')
                                                <label class="sr-only" for="inquiry-{{ $inquiry->id }}">{{ __('Inquiry status') }}</label>
                                                <select id="inquiry-{{ $inquiry->id }}" name="status">
                                                    @foreach (['new', 'contacted', 'closed'] as $inquiryStatus)
                                                        <option value="{{ $inquiryStatus }}" @selected($inquiry->status === $inquiryStatus)>{{ __(ucfirst($inquiryStatus)) }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="dash-btn-cancel">{{ __('Update') }}</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-ecommerce__filters {
            display: grid;
            grid-template-columns: minmax(12rem, 2fr) repeat(3, minmax(9rem, 1fr)) auto;
            align-items: end;
            gap: 0.75rem;
            margin: 0 0 1rem;
        }
        .admin-ecommerce__filters label { display: grid; gap: 0.3rem; color: #475569; font-size: 0.8125rem; font-weight: 600; }
        .admin-ecommerce__filters input, .admin-ecommerce__filters select,
        .admin-ecommerce__row-actions select, .admin-ecommerce__inquiry-action select {
            min-width: 0;
            padding: 0.5rem 0.65rem;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            background: #fff;
            color: #0f172a;
            font: inherit;
        }
        .admin-ecommerce__filter-actions, .admin-ecommerce__row-actions, .admin-ecommerce__inquiry-action {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.4rem;
        }
        .admin-ecommerce__table { min-width: 68rem; }
        .admin-ecommerce__row-actions { min-width: 14rem; }
        .admin-ecommerce__row-actions form { display: flex; flex-wrap: wrap; gap: 0.35rem; }
        .admin-ecommerce__message { min-width: 14rem; max-width: 22rem; white-space: normal; }
        @media (max-width: 1050px) {
            .admin-ecommerce__filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 520px) {
            .admin-ecommerce__filters { grid-template-columns: 1fr; }
        }
    </style>
@endpush
