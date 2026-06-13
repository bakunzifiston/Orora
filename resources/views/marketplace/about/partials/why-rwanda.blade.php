<section class="ab-section ab-rwanda">
    <div class="mp-container">
        <div class="ab-rwanda__grid">
            <div class="ab-rwanda__copy">
                <h2 class="ab-section__title">{{ $about['why_rwanda']['title'] }}</h2>
                @foreach ($about['why_rwanda']['paragraphs'] as $paragraph)
                    <p class="ab-prose">{{ $paragraph }}</p>
                @endforeach
            </div>
            <div class="ab-rwanda__visual">
                <img
                    src="{{ asset($about['why_rwanda']['image']) }}"
                    alt="{{ $about['why_rwanda']['image_alt'] }}"
                    loading="lazy"
                    class="ab-rwanda__image"
                >
            </div>
        </div>
    </div>
</section>
