<section class="learn-newsletter" id="newsletter">
    <div class="mp-container learn-newsletter__inner">
        <div>
            <h2 class="learn-newsletter__title">Get farming tips in your inbox</h2>
            <p class="learn-newsletter__text">Subscribe for articles, videos, and guides from livestock experts.</p>
        </div>

        @if (session('learning_subscribed'))
            <div class="mp-alert mp-alert--success" role="status">Thank you for subscribing!</div>
        @endif

        <form method="POST" action="{{ route('marketplace.learning.subscribe') }}" class="learn-newsletter__form">
            @csrf
            <input type="email" name="email" placeholder="Enter your email" value="{{ old('email') }}" required aria-label="Email address">
            <button type="submit" class="lp-btn lp-btn--gold">Subscribe</button>
        </form>
        @error('email')
            <p class="mp-field-error">{{ $message }}</p>
        @enderror
    </div>
</section>
