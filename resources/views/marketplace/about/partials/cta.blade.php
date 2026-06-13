<section class="ab-cta">
    <div class="mp-container ab-cta__inner">
        <h2 class="ab-cta__title">{{ $about['cta']['title'] }}</h2>
        <p class="ab-cta__subtitle">{{ $about['cta']['subtitle'] }}</p>
        <div class="ab-cta__actions">
            <a href="{{ route($about['cta']['primary_cta']['route']) }}" class="ab-btn ab-btn--dark ab-btn--lg">
                {{ $about['cta']['primary_cta']['label'] }} <span aria-hidden="true">→</span>
            </a>
            <a href="{{ route($about['cta']['secondary_cta']['route']) }}" class="ab-btn ab-btn--outline-dark ab-btn--lg">
                {{ $about['cta']['secondary_cta']['label'] }}
            </a>
        </div>
    </div>
</section>
