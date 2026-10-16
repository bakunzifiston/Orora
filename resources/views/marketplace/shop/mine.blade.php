@extends('layouts.marketplace')

@section('title', 'My listings')

@section('content')
    <section class="shop-form-page">
        <div class="mp-container shop-form-page__inner">
            <header class="shop-form-page__header">
                <h1>My animal listings</h1>
                <p>Track review status and update your listings.</p>
                <a href="{{ route('marketplace.shop.create') }}" class="lp-btn lp-btn--primary">Submit a listing</a>
            </header>

            @if (session('success'))
                <div class="mp-alert mp-alert--success" role="status">{{ session('success') }}</div>
            @endif

            @if ($listings->isEmpty())
                <div class="mp-alert">You have not submitted any listings yet.</div>
            @else
                <div class="shop-mine__table-wrap">
                    <table class="shop-mine__table">
                        <thead>
                            <tr>
                                <th>Animal</th>
                                <th>Farm</th>
                                <th>Price</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listings as $listing)
                                <tr>
                                    <td>
                                        <strong>{{ $listing->title }}</strong>
                                        <small>{{ $listing->animal_species ? ucfirst($listing->animal_species).' · ' : '' }}{{ $listing->breed ?: $listing->listing_code }}</small>
                                    </td>
                                    <td>{{ $listing->farm?->name ?? '—' }}</td>
                                    <td>{{ number_format((float) $listing->price) }} RWF</td>
                                    <td>{{ $listing->created_at->format('M j, Y') }}</td>
                                    <td>{{ __(config('marketplace.shop.listing_statuses.'.$listing->status, ucfirst(str_replace('_', ' ', $listing->status)))) }}</td>
                                    <td>
                                        <a href="{{ route('marketplace.shop.edit', $listing) }}">Edit</a>
                                        @if ($listing->status === 'active')
                                            · <a href="{{ route('marketplace.shop.show', $listing) }}">View in shop</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $listings->links() }}
            @endif
        </div>
    </section>
@endsection

@push('styles')
    <style>
        .shop-mine__table-wrap { overflow-x: auto; }
        .shop-mine__table { width: 100%; min-width: 42rem; border-collapse: collapse; background: #fff; }
        .shop-mine__table th, .shop-mine__table td { padding: 0.8rem; border-bottom: 1px solid #e5e7eb; text-align: left; }
        .shop-mine__table th { color: #64748b; font-size: 0.78rem; }
        .shop-mine__table td small { display: block; margin-top: 0.25rem; color: #64748b; }
    </style>
@endpush
