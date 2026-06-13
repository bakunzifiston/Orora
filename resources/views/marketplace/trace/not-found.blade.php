@extends('layouts.marketplace')

@section('title', 'Animal Not Found')

@section('content')
    <section class="tr-not-found">
        <div class="mp-container tr-not-found__inner">
            <div class="tr-not-found__icon" aria-hidden="true">❌</div>
            <h1 class="tr-not-found__title">Animal Not Found</h1>
            <p class="tr-not-found__text">
                No animal found with tag number<br>
                <strong>"{{ $tagNumber }}"</strong>
            </p>
            <p class="tr-not-found__help">
                Please check the tag number and try again.
                If you believe this is an error, contact the farm directly or reach out to our support team.
            </p>
            <div class="tr-not-found__actions">
                <a href="{{ route('marketplace.trace') }}" class="lp-btn lp-btn--primary">Try Again</a>
                <a href="{{ route('marketplace.contact') }}" class="lp-btn lp-btn--outline">Contact Support</a>
            </div>
        </div>
    </section>
@endsection
