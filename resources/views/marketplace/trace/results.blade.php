@extends('layouts.marketplace')

@section('title', 'Trace Results')

@section('content')
    <section class="tr-hero tr-hero--compact">
        <div class="mp-container tr-hero__inner">
            <h1 class="tr-hero__title">Multiple animals found</h1>
            <p class="tr-hero__lead">
                Tag <strong>{{ $tagNumber }}</strong> matches more than one record.
                Select the correct animal below.
            </p>
        </div>
    </section>

    <section class="tr-results">
        <div class="mp-container">
            <div class="tr-results__grid">
                @foreach ($matches as $animal)
                    <a href="{{ route('marketplace.trace.show', $animal) }}" class="tr-result-card">
                        <div class="tr-result-card__header">
                            <span class="tr-result-card__tag">{{ $animal->tag_number }}</span>
                            <span class="tr-result-card__status">{{ $animal->lifecycle_status }}</span>
                        </div>
                        <h2 class="tr-result-card__name">{{ $animal->name ?: 'Unnamed animal' }}</h2>
                        <p class="tr-result-card__meta">
                            {{ $animal->species }} · {{ $animal->breed ?: 'Unknown breed' }}
                        </p>
                        <p class="tr-result-card__farm">
                            {{ $animal->farm?->name }} — {{ $animal->farm?->district ?: $animal->farm?->province ?: 'Rwanda' }}
                        </p>
                    </a>
                @endforeach
            </div>

            <p class="tr-results__back">
                <a href="{{ route('marketplace.trace') }}">← Search again</a>
            </p>
        </div>
    </section>
@endsection
