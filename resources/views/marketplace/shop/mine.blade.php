@extends('layouts.marketplace')

@section('title', 'My listings')

@section('content')
    <section class="shop-form-page">
        <div class="mp-container shop-form-page__inner shop-form-page__inner--wide">
            <header class="shop-form-page__header">
                <div>
                    <h1>My listings</h1>
                    <p>Track review status, update your listings and reply to buyers.</p>
                </div>
                <a href="{{ route('marketplace.shop.create') }}" class="lp-btn lp-btn--primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2.25" stroke-linecap="round"/>
                    </svg>
                    Submit a listing
                </a>
            </header>

            @if (session('success'))
                <div class="mp-alert mp-alert--success" role="status">{{ session('success') }}</div>
            @endif

            <div class="shop-mine">
                @if ($listings->isEmpty())
                    <div class="shop-mine__empty">
                        <div class="shop-empty__icon" aria-hidden="true">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                                <path d="M4 7h16v12H4V7z" stroke="currentColor" stroke-width="1.75"/>
                                <path d="M8 7V5a4 4 0 018 0v2" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <h2>No listings yet</h2>
                        <p>List an animal or farm product and it will appear here while it is reviewed.</p>
                        <a href="{{ route('marketplace.shop.create') }}" class="lp-btn lp-btn--primary">Submit your first listing</a>
                    </div>
                @else
                    <div class="shop-mine__table-wrap">
                        <table class="shop-mine__table">
                            <thead>
                                <tr>
                                    <th scope="col">Listing</th>
                                    <th scope="col">Farm</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Submitted</th>
                                    <th scope="col">Inquiries</th>
                                    <th scope="col">Status</th>
                                    <th scope="col"><span class="sr-only">Actions</span></th>
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
                                        <td class="shop-mine__price">{{ number_format((float) $listing->price) }} RWF</td>
                                        <td>{{ $listing->created_at->format('M j, Y') }}</td>
                                        <td>
                                            @if ($listing->inquiries_count > 0)
                                                <a href="#inquiries" class="shop-mine__inquiry-count">
                                                    {{ $listing->inquiries_count }}
                                                    @if ($listing->new_inquiries_count > 0)
                                                        <span>{{ $listing->new_inquiries_count }} new</span>
                                                    @endif
                                                </a>
                                            @else
                                                <span class="shop-mine__muted">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="shop-status shop-status--{{ $listing->status }}">
                                                {{ __(config('marketplace.shop.listing_statuses.'.$listing->status, ucfirst(str_replace('_', ' ', $listing->status)))) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="shop-mine__actions">
                                                <a href="{{ route('marketplace.shop.edit', $listing) }}">Edit</a>
                                                @if ($listing->status === 'active')
                                                    <a href="{{ route('marketplace.shop.show', $listing) }}">View in shop</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($listings->hasPages())
                        <div class="shop-mine__pagination">
                            {{ $listings->links('marketplace.shop.partials.pagination') }}
                        </div>
                    @endif
                @endif
            </div>

            <section class="shop-inquiries" id="inquiries" aria-labelledby="inquiries-title">
                <header class="shop-inquiries__head">
                    <h2 id="inquiries-title">Buyer inquiries</h2>
                    <p>Messages buyers sent from your listings. Call or email them back, then mark the inquiry as contacted.</p>
                </header>

                @if ($inquiries->isEmpty())
                    <p class="shop-inquiries__empty">No buyer inquiries yet. They will appear here, and in your notifications, when a buyer asks about a listing.</p>
                @else
                    <ul class="shop-inquiries__list">
                        @foreach ($inquiries as $inquiry)
                            @php($phone = preg_replace('/\s+/', '', (string) $inquiry->buyer_phone))
                            <li @class(['shop-inquiries__item', 'is-new' => $inquiry->status === 'new'])>
                                <div class="shop-inquiries__main">
                                    <p class="shop-inquiries__meta">
                                        <span class="shop-status shop-status--inquiry-{{ $inquiry->status }}">{{ ucfirst($inquiry->status) }}</span>
                                        <span>{{ $inquiry->listing?->title }}</span>
                                        <time datetime="{{ $inquiry->created_at->toIso8601String() }}">{{ $inquiry->created_at->diffForHumans() }}</time>
                                    </p>
                                    <p class="shop-inquiries__buyer">
                                        <strong>{{ $inquiry->buyer_name }}</strong>
                                        @if ($inquiry->buyer_location) · {{ $inquiry->buyer_location }}@endif
                                    </p>
                                    <p class="shop-inquiries__message">{{ $inquiry->message }}</p>
                                    <p class="shop-inquiries__contact">
                                        <a href="tel:{{ $phone }}">{{ $inquiry->buyer_phone }}</a>
                                        @if ($inquiry->buyer_email)
                                            <a href="mailto:{{ $inquiry->buyer_email }}">{{ $inquiry->buyer_email }}</a>
                                        @endif
                                    </p>
                                </div>
                                <form method="POST" action="{{ route('marketplace.shop.inquiries.update', $inquiry) }}" class="shop-inquiries__action">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="inquiry-status-{{ $inquiry->id }}">Inquiry status</label>
                                    <select id="inquiry-status-{{ $inquiry->id }}" name="status">
                                        @foreach (['new' => 'New', 'contacted' => 'Contacted', 'closed' => 'Closed'] as $value => $label)
                                            <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="lp-btn lp-btn--outline">Save</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </section>
@endsection
