<section class="ab-section ab-section--dark ab-values">
    <div class="mp-container">
        <h2 class="ab-section__title ab-section__title--center ab-section__title--light">Our Values</h2>
        <div class="ab-values__grid">
            @foreach ($about['values'] as $value)
                <article class="ab-value">
                    <span class="ab-value__icon" aria-hidden="true">{{ $value['icon'] }}</span>
                    <h3 class="ab-value__title">{{ $value['title'] }}</h3>
                    <p class="ab-value__text">{{ $value['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
