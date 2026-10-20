<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Orora</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/notification-bell.js'])
    @include('layouts.partials.dashboard-styles')
</head>
<body class="font-sans antialiased">
    <div class="dash-app">
        @include('layouts.partials.dash-mobile-nav')
        @include('layouts.partials.dashboard-sidebar', [
            'navigation' => $navigation ?? config('modules.navigation'),
            'navigationGroups' => $navigationGroups ?? config('modules.navigation_groups'),
            'activeNav' => $activeNav ?? 'dashboard',
        ])

        <div class="dash-main">
            <header class="dash-topbar">
                <button
                    type="button"
                    class="dash-nav-toggle"
                    data-dash-nav-toggle
                    aria-controls="dash-sidebar"
                    aria-expanded="false"
                    aria-label="{{ __('Open menu') }}"
                >
                    <span class="dash-nav-toggle__bars" aria-hidden="true">
                        <span></span><span></span><span></span>
                    </span>
                </button>
                <div class="dash-search">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="search" placeholder="{{ __('Search farms, animals, sales…') }}" aria-label="{{ __('Search farms, animals, sales…') }}">
                </div>
                <div class="dash-topbar-actions">
                    @include('layouts.partials.locale-switcher')
                    <button type="button" class="dash-icon-btn dash-icon-btn--desktop" aria-label="Security">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </button>
                    @auth
                        @include('layouts.partials.notification-bell')
                    @endauth

                    @auth
                        @php
                            $topbarInitials = collect(explode(' ', auth()->user()->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                        @endphp
                        <div class="dash-topbar-profile">
                            <a href="{{ route('profile.edit') }}" class="dash-topbar-profile__info" title="{{ __('Edit profile') }}">
                                <span class="dash-topbar-profile__avatar">{{ $topbarInitials }}</span>
                                <div class="dash-topbar-profile__text">
                                    <span class="dash-topbar-profile__name">{{ auth()->user()->name }}</span>
                                    <span class="dash-topbar-profile__role">{{ __('Edit profile') }}</span>
                                </div>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="dash-topbar-logout-form">
                                @csrf
                                <button type="submit" class="dash-topbar-logout" title="{{ __('Sign out') }}">{{ __('Logout') }}</button>
                            </form>
                        </div>
                    @endauth
                </div>
            </header>

            <div class="dash-content">
                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
