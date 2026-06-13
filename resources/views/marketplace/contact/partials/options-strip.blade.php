<section class="ct-options">
    <div class="mp-container ct-options__grid">
        <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="ct-option">
            <span class="ct-option__icon" aria-hidden="true">📞</span>
            <h2 class="ct-option__title">Call Us</h2>
            <p class="ct-option__value">{{ $contact['phone_display'] }}</p>
        </a>
        <a href="mailto:{{ $contact['email'] }}" class="ct-option">
            <span class="ct-option__icon" aria-hidden="true">📧</span>
            <h2 class="ct-option__title">Email Us</h2>
            <p class="ct-option__value">{{ $contact['email'] }}</p>
        </a>
        <a href="{{ $contact['maps_url'] }}" class="ct-option" target="_blank" rel="noopener noreferrer">
            <span class="ct-option__icon" aria-hidden="true">📍</span>
            <h2 class="ct-option__title">Visit Us</h2>
            <p class="ct-option__value">{{ $contact['address'] }}</p>
        </a>
    </div>
</section>
