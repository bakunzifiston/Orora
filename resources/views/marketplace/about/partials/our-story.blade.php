<section class="ab-section ab-story">
    <div class="mp-container">
        <div class="ab-story__grid">
            <div class="ab-story__visual">
                <img
                    src="{{ asset($about['story']['image']) }}"
                    alt="{{ $about['story']['image_alt'] }}"
                    loading="lazy"
                    class="ab-story__image"
                >
            </div>
            <div class="ab-story__copy">
                <h2 class="ab-section__title">{{ $about['story']['title'] }}</h2>
                @foreach ($about['story']['paragraphs'] as $paragraph)
                    <p class="ab-prose">{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </div>
</section>
