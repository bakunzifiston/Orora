@php
    $activePage = $activePage ?? '';
    $navItems = [
        ['key' => 'home', 'label' => 'Home', 'route' => 'marketplace.home'],
        ['key' => 'shop', 'label' => 'Shop', 'route' => 'marketplace.shop'],
        ['key' => 'about', 'label' => 'About', 'route' => 'marketplace.about'],
        ['key' => 'contact', 'label' => 'Contact', 'route' => 'marketplace.contact'],
    ];
@endphp

<header class="lp-nav" data-lp-nav>
    <div class="mp-container lp-nav__inner">
        <a href="{{ route('marketplace.home') }}" class="lp-logo" aria-label="Orora Farm home">
            @include('partials.orora-logo', ['class' => 'lp-logo__image', 'alt' => 'Orora Farm'])
        </a>

        <nav class="lp-nav__links" aria-label="Main navigation">
            @foreach ($navItems as $item)
                <a
                    href="{{ route($item['route']) }}"
                    class="lp-nav__link {{ $activePage === $item['key'] ? 'is-active' : '' }}"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="lp-nav__actions">
            @auth
                <a href="{{ route('dashboard') }}" class="lp-btn lp-btn--outline-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="lp-btn lp-btn--outline-sm">Login</a>
                <a href="{{ route('register') }}" class="lp-btn lp-btn--gold">Get Started</a>
            @endauth
        </div>

        <button type="button" class="lp-nav__toggle" aria-label="Open menu" aria-expanded="false" data-lp-nav-toggle>
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="lp-nav__mobile" data-lp-nav-mobile hidden>
        @foreach ($navItems as $item)
            <a
                href="{{ route($item['route']) }}"
                class="lp-nav__mobile-link {{ $activePage === $item['key'] ? 'is-active' : '' }}"
            >
                {{ $item['label'] }}
            </a>
        @endforeach
        <div class="lp-nav__mobile-actions">
            @auth
                <a href="{{ route('dashboard') }}" class="lp-btn lp-btn--gold lp-btn--block">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="lp-btn lp-btn--outline lp-btn--block">Login</a>
                <a href="{{ route('register') }}" class="lp-btn lp-btn--gold lp-btn--block">Get Started</a>
            @endauth
        </div>
    </div>
</header>
