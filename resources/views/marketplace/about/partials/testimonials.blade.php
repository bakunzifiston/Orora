<section class="ab-section ab-section--tint ab-testimonials" data-ab-testimonials>
    <div class="mp-container">
        <h2 class="ab-section__title ab-section__title--center">What Farmers Say</h2>

        <div class="ab-testimonials__slider">
            @foreach ($testimonials as $index => $testimonial)
                <blockquote class="ab-testimonial {{ $index === 0 ? 'is-active' : '' }}" data-testimonial-slide="{{ $index }}">
                    <span class="ab-testimonial__mark" aria-hidden="true">❝</span>
                    <p class="ab-testimonial__quote">{{ $testimonial['quote'] }}</p>
                    <footer class="ab-testimonial__author">
                        <div class="ab-testimonial__avatar" aria-hidden="true">{{ $testimonial['initials'] }}</div>
                        <div>
                            <cite class="ab-testimonial__name">{{ $testimonial['name'] }}</cite>
                            <p class="ab-testimonial__role">{{ $testimonial['role'] }} — {{ $testimonial['location'] }}, Rwanda</p>
                        </div>
                    </footer>
                </blockquote>
            @endforeach
        </div>

        <div class="ab-testimonials__dots">
            @foreach ($testimonials as $index => $testimonial)
                <button
                    type="button"
                    class="ab-testimonials__dot {{ $index === 0 ? 'is-active' : '' }}"
                    data-testimonial-dot="{{ $index }}"
                    aria-label="Show testimonial {{ $index + 1 }}"
                ></button>
            @endforeach
        </div>
    </div>
</section>
