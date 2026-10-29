<footer class="lp-footer">
    <div class="mp-container lp-footer__grid">
        <div class="lp-footer__brand">
            <a href="{{ route('marketplace.home') }}" class="lp-logo lp-logo--footer" aria-label="Orora Farm home">
                @include('partials.orora-logo', ['class' => 'lp-logo__image lp-footer__logo', 'alt' => 'Orora Farm'])
            </a>
        </div>

        <div>
            <h3 class="lp-footer__heading">Product</h3>
            <ul class="lp-footer__links">
                <li><a href="{{ route('marketplace.home') }}#features">Features</a></li>
                <li><a href="{{ route('marketplace.shop') }}">Marketplace</a></li>
                <li><a href="{{ route('marketplace.home') }}#pricing">Pricing</a></li>
                <li><a href="{{ route('register') }}">Register</a></li>
            </ul>
        </div>

        <div>
            <h3 class="lp-footer__heading">Company</h3>
            <ul class="lp-footer__links">
                <li><a href="{{ route('marketplace.about') }}">About</a></li>
                <li><a href="{{ route('marketplace.contact') }}">Contact</a></li>
                <li><a href="{{ route('marketplace.contact') }}">Careers</a></li>
                <li><a href="{{ route('marketplace.contact') }}">Privacy Policy</a></li>
                <li><a href="{{ route('marketplace.contact') }}">Terms of Use</a></li>
            </ul>
        </div>

        <div>
            <h3 class="lp-footer__heading">Resources</h3>
            <ul class="lp-footer__links">
                <li><a href="{{ route('marketplace.contact') }}">Documentation</a></li>
                <li><a href="{{ route('marketplace.contact') }}">Support</a></li>
            </ul>
        </div>
    </div>

    <div class="mp-container lp-footer__contact">
        <p class="mp-footer__contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0119 9.5C19 14.8 12 21 12 21z" stroke="currentColor" stroke-width="1.8"/><circle cx="12" cy="9.5" r="2.5" stroke="currentColor" stroke-width="1.8"/></svg> {{ config('marketplace.contact.address') }}</p>
        <p class="mp-footer__contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 6.5L12 13l8.5-6.5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg> <a href="mailto:{{ config('marketplace.contact.email') }}">{{ config('marketplace.contact.email') }}</a></p>
        <p class="mp-footer__contact-item"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg> {{ config('marketplace.contact.phone_display', config('marketplace.contact.phone')) }}</p>
    </div>

    <div class="mp-container lp-footer__social">
        @foreach (config('marketplace.social', []) as $social)
            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>
        @endforeach
    </div>

    <div class="mp-container lp-footer__bottom">
        <p>&copy; {{ date('Y') }} Orora Farm. All rights reserved.</p>
    </div>
</footer>
