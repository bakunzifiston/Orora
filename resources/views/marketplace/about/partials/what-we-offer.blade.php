<section class="ab-section ab-offer">
    <div class="mp-container">
        <div class="ab-section__header ab-section__header--center">
            <h2 class="ab-section__title">What We Offer</h2>
            <p class="ab-section__subtitle">Everything your farm needs in one platform</p>
        </div>
        <div class="ab-offer__grid">
            @foreach ($about['offerings'] as $offering)
                <article class="ab-offer-card">
                    <span class="ab-offer-card__icon" aria-hidden="true">{{ $offering['icon'] }}</span>
                    <h3 class="ab-offer-card__title">{{ $offering['title'] }}</h3>
                    <p class="ab-offer-card__text">{{ $offering['description'] }}</p>
                    @if (! empty($offering['route']))
                        <a href="{{ route($offering['route']) }}" class="ab-offer-card__link">Learn more →</a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
