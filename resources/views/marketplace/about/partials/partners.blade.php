<section class="ab-section ab-partners">
    <div class="mp-container">
        <h2 class="ab-section__title ab-section__title--center">Our Partners</h2>
        <div class="ab-partners__grid">
            @foreach ($about['partners'] as $partner)
                <div class="ab-partner" title="{{ $partner['name'] }}">
                    <span class="ab-partner__logo" aria-hidden="true">{{ $partner['initials'] }}</span>
                    <span class="ab-partner__name">{{ $partner['name'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>
