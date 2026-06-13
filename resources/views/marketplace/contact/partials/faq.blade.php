<section class="ct-faq">
    <div class="mp-container">
        <div class="ct-faq__header">
            <h2 class="ct-faq__title">Frequently Asked Questions</h2>
            <p class="ct-faq__subtitle">Quick answers before you reach out</p>
        </div>

        <div class="ct-faq__list" data-ct-faq>
            @foreach ($contact['faq'] as $index => $item)
                <div class="ct-faq__item {{ $index === 0 ? 'is-open' : '' }}">
                    <button
                        type="button"
                        class="ct-faq__question"
                        aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                        data-faq-toggle
                    >
                        <span class="ct-faq__arrow" aria-hidden="true">▶</span>
                        {{ $item['question'] }}
                    </button>
                    <div class="ct-faq__answer" data-faq-answer>
                        <p>{{ $item['answer'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="ct-faq__footer">
            Still have questions?
            <a href="#contact-form">Send us a message ↑</a>
        </p>
    </div>
</section>
