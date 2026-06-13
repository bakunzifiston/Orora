<section class="ab-section ab-section--tint ab-mission">
    <div class="mp-container">
        <h2 class="ab-section__title ab-section__title--center">Our Mission</h2>
        <blockquote class="ab-mission__quote">
            <span class="ab-mission__mark" aria-hidden="true">❝</span>
            {{ $about['mission']['quote'] }}
            <span class="ab-mission__mark" aria-hidden="true">❞</span>
        </blockquote>
        <div class="ab-mission__pillars">
            @foreach ($about['mission']['pillars'] as $pillar)
                <article class="ab-mission__pillar">
                    <span class="ab-mission__pillar-icon" aria-hidden="true">{{ $pillar['icon'] }}</span>
                    <h3 class="ab-mission__pillar-title">{{ $pillar['title'] }}</h3>
                    <p class="ab-mission__pillar-text">{{ $pillar['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
