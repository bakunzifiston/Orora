@extends('layouts.marketplace')

@section('title', 'Edit Listing')

@section('content')
    <section class="shop-form-page">
        <div class="mp-container shop-form-page__inner">
            <header class="shop-form-page__header">
                <h1>Edit Listing</h1>
                <p>{{ $listing->listing_code }}</p>
            </header>

            @if ($errors->any())
                <div class="mp-alert" role="alert">
                    Please fix the errors below and try again.
                </div>
            @endif

            @include('marketplace.shop.partials._form', ['listing' => $listing])

            <form method="POST" action="{{ route('marketplace.shop.destroy', $listing) }}" class="shop-form-delete" onsubmit="return confirm('Remove this listing?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="lp-btn lp-btn--outline">Remove Listing</button>
            </form>
        </div>
    </section>
@endsection
