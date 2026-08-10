<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin') — Orora</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.dashboard-styles')
    @include('central.partials.data-table-styles')
    @stack('styles')
</head>
<body class="font-sans antialiased admin-app">
    @php
        $admin = auth('admin')->user();
        $initials = collect(explode(' ', $admin->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
    @endphp
    <div class="dash-app">
        @include('layouts.partials.admin-sidebar', [
            'activeNav' => $activeNav ?? 'dashboard',
        ])

        <div class="dash-main">
            <header class="dash-topbar">
                <p style="margin: 0; font-size: 0.8125rem; font-weight: 600; color: var(--orora-gray);">
                    {{ __('Platform workspace') }}
                </p>
                <div class="dash-topbar-actions">
                    @include('layouts.partials.locale-switcher')
                    <a href="{{ url('/') }}" target="_blank" rel="noopener" class="dash-topbar-logout" style="text-decoration: none;">
                        {{ __('Public site') }}
                    </a>
                    <div class="dash-topbar-profile">
                        <div class="dash-topbar-profile__info">
                            <span class="dash-topbar-profile__avatar">{{ $initials }}</span>
                            <div class="dash-topbar-profile__text">
                                <span class="dash-topbar-profile__name">{{ $admin->name }}</span>
                                <span class="dash-topbar-profile__role">{{ __('Super admin') }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('central.logout') }}" class="dash-topbar-logout-form">
                            @csrf
                            <button type="submit" class="dash-topbar-logout" title="{{ __('Sign out') }}">{{ __('Logout') }}</button>
                        </form>
                    </div>
                </div>
            </header>

            <div class="dash-content">
                @include('modules.partials.flash')
                @yield('content')
            </div>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
