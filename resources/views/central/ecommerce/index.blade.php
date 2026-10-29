@extends('layouts.admin')

@section('title', __('Marketplace'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-ecommerce">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar admin-ecommerce__head">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ __('Marketplace') }}</h1>
                <p class="admin-dash__period">{{ __('Review every shop listing and connect buyers with farmers.') }}</p>
            </div>
            <a href="{{ route('central.ecommerce.listings.create') }}" class="dash-btn-save admin-ecommerce__create">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/></svg>
                {{ __('Create animal listing') }}
            </a>
        </div>

        <section class="farm-dash__section" aria-label="{{ __('Marketplace summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--5">
                @foreach ([
                    ['label' => 'Total listings', 'value' => $stats['total'], 'icon' => 'box', 'tone' => 'stock'],
                    ['label' => 'Pending review', 'value' => $stats['pending'], 'icon' => 'health', 'tone' => 'receivable'],
                    ['label' => 'Published', 'value' => $stats['published'], 'icon' => 'sale', 'tone' => 'production'],
                    ['label' => 'Sold or inactive', 'value' => $stats['inactive'], 'icon' => 'animal', 'tone' => 'expense'],
                    ['label' => 'New buyer inquiries', 'value' => $stats['new_inquiries'], 'icon' => 'mail', 'tone' => 'sales'],
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

        <section class="farm-dash__section" aria-label="{{ __('Shop listings') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Shop listings') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($listings->total()) }} {{ __('total') }}</p>
                    </div>
                </header>

                <nav class="admin-ecommerce__types" aria-label="{{ __('Listing categories') }}">
                    <a href="{{ route('central.ecommerce.index', array_filter([...$filters, 'type' => null])) }}" @class(['is-active' => empty($filters['type'])])>
                        {{ __('All') }} <span>{{ number_format($typeCounts->sum()) }}</span>
                    </a>
                    @foreach ($types as $typeKey => $typeLabel)
                        <a href="{{ route('central.ecommerce.index', array_filter([...$filters, 'type' => $typeKey])) }}" @class(['is-active' => ($filters['type'] ?? '') === $typeKey])>
                            {{ __($typeLabel) }} <span>{{ number_format($typeCounts[$typeKey] ?? 0) }}</span>
                        </a>
                    @endforeach
                </nav>

                <form method="GET" action="{{ route('central.ecommerce.index') }}" class="admin-ecommerce__filters">
                    @if (! empty($filters['type']))
                        <input type="hidden" name="type" value="{{ $filters['type'] }}">
                    @endif
                    <label>
                        <span>{{ __('Search') }}</span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Title, farmer, phone, location') }}">
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
                    <label @class(['is-hidden' => filled($filters['type'] ?? null) && $filters['type'] !== 'animal'])>
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
                        <button type="submit" class="dash-btn-save">{{ __('Filter') }}</button>
                        <a href="{{ route('central.ecommerce.index') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                    </div>
                </form>

                @if ($listings->isEmpty())
                    <p class="dash-empty">{{ __('No listings match these filters.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table admin-ecommerce__table">
                            <thead>
                                <tr>
                                    <th>{{ __('Listing') }}</th>
                                    <th>{{ __('Farmer / farm') }}</th>
                                    <th>{{ __('Location') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Submitted') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="health-table__actions">{{ __('Actions') }}</th>
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
                                                    <span class="health-table__pill health-table__pill--muted">{{ __($types[$listing->listing_type] ?? ucfirst(str_replace('_', ' ', (string) $listing->listing_type))) }}</span>
                                                </span>
                                                @if ($listing->listing_type === 'animal')
                                                <span class="health-table__meta">
                                                    {{ $speciesLabel ? __(ucfirst($speciesLabel)) : __('Animal') }}
                                                    @if ($listing->breed) · {{ $listing->breed }}@endif
                                                    @if ($listing->animal_sex || $listing->animal?->gender) · {{ __(ucfirst($listing->animal_sex ?: $listing->animal->gender)) }}@endif
                                                    @if ($listing->age) · {{ $listing->age }}@endif
                                                    @if ($listing->animal) · {{ $listing->animal->tag_number }}@endif
                                                </span>
                                                @endif
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
                                        <td class="health-table__actions">
                                            @php
                                                // One-click status steps; reasons and notes are added on the Manage page.
                                                $quickActions = match ($listing->status) {
                                                    'pending_review', 'pending' => [
                                                        ['status' => 'approved', 'label' => __('Approve'), 'tone' => 'primary'],
                                                        ['status' => 'rejected', 'label' => __('Reject'), 'tone' => 'danger', 'confirm' => __('Reject this listing? The seller will see it as rejected.')],
                                                    ],
                                                    'approved' => [
                                                        ['status' => 'active', 'label' => __('Publish'), 'tone' => 'primary'],
                                                        ['status' => 'rejected', 'label' => __('Reject'), 'tone' => 'danger', 'confirm' => __('Reject this listing? The seller will see it as rejected.')],
                                                    ],
                                                    'active' => [
                                                        ['status' => 'sold', 'label' => __('Mark sold'), 'tone' => 'default', 'confirm' => __('Mark this listing as sold? It will be removed from the shop.')],
                                                        ['status' => 'inactive', 'label' => __('Unpublish'), 'tone' => 'danger', 'confirm' => __('Unpublish this listing? It will be removed from the shop.')],
                                                    ],
                                                    'draft', 'rejected', 'inactive', 'sold' => [
                                                        ['status' => 'approved', 'label' => __('Approve'), 'tone' => 'primary'],
                                                    ],
                                                    default => [],
                                                };
                                            @endphp
                                            <div class="health-table__action-btns admin-ecommerce__actions">
                                                @foreach ($quickActions as $action)
                                                    <form
                                                        method="POST"
                                                        action="{{ route('central.ecommerce.listings.status', $listing) }}"
                                                        @if (! empty($action['confirm'])) onsubmit="return confirm(@js($action['confirm']));" @endif
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $action['status'] }}">
                                                        <button type="submit" @class([
                                                            'dash-farm-card__btn',
                                                            'admin-ecommerce__btn--primary' => $action['tone'] === 'primary',
                                                            'dash-farm-card__btn--danger' => $action['tone'] === 'danger',
                                                        ])>{{ $action['label'] }}</button>
                                                    </form>
                                                @endforeach
                                                @if ($listing->status === 'active')
                                                    <a href="{{ route('marketplace.shop.show', $listing) }}" class="dash-farm-card__btn" target="_blank" rel="noopener" title="{{ __('Open in the public shop') }}">{{ __('View in shop') }}</a>
                                                @endif
                                                @if ($listing->listing_type === 'animal')
                                                    <a href="{{ route('central.ecommerce.listings.edit', $listing) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                                @endif
                                                <a href="{{ route('central.ecommerce.listings.show', $listing) }}" class="dash-farm-card__btn">{{ __('Manage') }}</a>
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
        .admin-ecommerce__head { flex-direction: row !important; align-items: center !important; justify-content: space-between; flex-wrap: wrap; }
        .admin-ecommerce__head .admin-dash__header-text { flex: 1 1 18rem !important; }
        .admin-ecommerce__head .admin-dash__period { white-space: normal; overflow: visible; text-overflow: clip; }
        .admin-ecommerce__create { display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap; }
        .admin-ecommerce__filters .is-hidden { display: none; }
        .admin-ecommerce__link { color: #0f3d3d; font-size: 0.8125rem; font-weight: 700; text-decoration: none; }
        .admin-ecommerce__link:hover { text-decoration: underline; }
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
            height: 2.6rem;
            padding: 0 0.7rem;
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
        .admin-ecommerce__row-actions { gap: 0.85rem; }
        .admin-ecommerce__filter-actions .dash-btn-save, .admin-ecommerce__filter-actions .dash-btn-cancel {
            display: inline-flex; align-items: center; height: 2.6rem; padding: 0 1rem;
        }
        .admin-ecommerce__table { min-width: 64rem; }
        .admin-ecommerce__types { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0 0 1rem; }
        .admin-ecommerce__types a {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.4rem 0.8rem; border: 1px solid #cbd5e1; border-radius: 999px;
            color: #334155; font-size: 0.8125rem; font-weight: 600; text-decoration: none;
        }
        .admin-ecommerce__types a span { padding: 0 0.4rem; border-radius: 999px; background: #f1f5f9; font-size: 0.75rem; }
        .admin-ecommerce__types a.is-active { background: #002b2b; border-color: #002b2b; color: #fff; }
        .admin-ecommerce__types a.is-active span { background: #a4d400; color: #002b2b; }
        .admin-ecommerce__table .health-table__actions { width: 17rem; min-width: 17rem; white-space: normal; }
        .admin-ecommerce__actions { display: flex !important; flex-wrap: wrap; justify-content: flex-end; width: 100%; }
        .admin-ecommerce__btn--primary { background: #a4d400 !important; border-color: #a4d400 !important; color: #002b2b !important; }
        .admin-ecommerce__btn--primary:hover { background: #8fb300 !important; border-color: #8fb300 !important; }
        .admin-ecommerce__row-actions form { margin: 0; }
        .admin-ecommerce__message { min-width: 14rem; max-width: 22rem; white-space: normal; }
        @media (max-width: 1050px) {
            .admin-ecommerce__filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 520px) {
            .admin-ecommerce__filters { grid-template-columns: 1fr; }
        }
    </style>
@endpush
