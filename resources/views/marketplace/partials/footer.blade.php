<footer class="lp-footer">
    <div class="mp-container lp-footer__grid">
        <div class="lp-footer__brand">
            <a href="{{ route('marketplace.home') }}" class="lp-logo lp-logo--footer">
                <span class="lp-logo__icon" aria-hidden="true">🌿</span>
                <span class="lp-logo__text">Orora Farm</span>
            </a>
            <p class="lp-footer__tagline">Farm management made simple.</p>
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
        <p>📍 {{ config('marketplace.contact.address') }}</p>
        <p>📧 <a href="mailto:{{ config('marketplace.contact.email') }}">{{ config('marketplace.contact.email') }}</a></p>
        <p>📞 {{ config('marketplace.contact.phone_display', config('marketplace.contact.phone')) }}</p>
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
