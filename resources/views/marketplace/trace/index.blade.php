@extends('layouts.marketplace')

@section('title', 'Trace an Animal')

@section('meta_description', 'Look up any Orora Farm animal by tag number. View health, vaccination, movement, and certificate history — free, no login required.')

@section('content')
    <section class="tr-hero">
        <div class="mp-container tr-hero__inner">
            <p class="tr-hero__eyebrow">Public traceability</p>
            <h1 class="tr-hero__title">Trace an Animal</h1>
            <p class="tr-hero__lead">
                Enter an ear tag or ID number to view the full animal profile —
                health records, vaccinations, movements, and certificates.
                No account needed.
            </p>

            <form method="POST" action="{{ route('marketplace.trace.lookup') }}" class="tr-search">
                @csrf
                <label for="tag_number" class="tr-search__label">Animal tag number</label>
                <div class="tr-search__row">
                    <input
                        type="text"
                        id="tag_number"
                        name="tag_number"
                        value="{{ old('tag_number') }}"
                        placeholder="e.g. RW-2024-00482"
                        class="tr-search__input"
                        required
                        autofocus
                        autocomplete="off"
                    >
                    <button type="submit" class="lp-btn lp-btn--primary lp-btn--lg">
                        Look Up <span aria-hidden="true">→</span>
                    </button>
                </div>
                @error('tag_number')
                    <p class="tr-search__error" role="alert">{{ $message }}</p>
                @enderror
            </form>

            <ul class="tr-hero__audience">
                <li>Buyers verifying origin</li>
                <li>Vets checking health history</li>
                <li>Inspectors reviewing compliance</li>
            </ul>
        </div>
    </section>

    <section class="tr-info">
        <div class="mp-container tr-info__grid">
            <article class="tr-info__card">
                <span class="tr-info__icon" aria-hidden="true">🔍</span>
                <h2 class="tr-info__title">Instant lookup</h2>
                <p class="tr-info__text">Search by tag number and get the complete registered profile in seconds.</p>
            </article>
            <article class="tr-info__card">
                <span class="tr-info__icon" aria-hidden="true">🏥</span>
                <h2 class="tr-info__title">Health &amp; vaccines</h2>
                <p class="tr-info__text">Vaccination dates, treatments, vet visits, and current health status.</p>
            </article>
            <article class="tr-info__card">
                <span class="tr-info__icon" aria-hidden="true">📄</span>
                <h2 class="tr-info__title">Download PDF</h2>
                <p class="tr-info__text">Export the full traceability report as a PDF for records or inspections.</p>
            </article>
        </div>
    </section>
@endsection

@push('body-scripts')
    @vite('resources/js/marketplace-trace.js')
@endpush
