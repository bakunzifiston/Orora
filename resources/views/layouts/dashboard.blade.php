<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Orora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.dashboard-styles')
</head>
<body class="font-sans antialiased">
    <div class="dash-app">
        @include('layouts.partials.dashboard-sidebar', [
            'navigation' => $navigation ?? config('modules.navigation'),
            'activeNav' => $activeNav ?? 'dashboard',
        ])

        <div class="dash-main">
            <header class="dash-topbar">
                <div class="dash-search">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="search" placeholder="Search farms, animals, sales…" aria-label="Search">
                </div>
                <div class="dash-topbar-actions">
                    <button type="button" class="dash-icon-btn" aria-label="Security">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </button>
                    <button type="button" class="dash-icon-btn" aria-label="Notifications">
                        <span class="dot"></span>
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    </button>

                    @auth
                        @php
                            $topbarInitials = collect(explode(' ', auth()->user()->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                        @endphp
                        <div class="dash-topbar-profile">
                            <a href="{{ route('profile.edit') }}" class="dash-topbar-profile__info" title="Edit profile">
                                <span class="dash-topbar-profile__avatar">{{ $topbarInitials }}</span>
                                <div class="dash-topbar-profile__text">
                                    <span class="dash-topbar-profile__name">{{ auth()->user()->name }}</span>
                                    <span class="dash-topbar-profile__role">Edit profile</span>
                                </div>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="dash-topbar-logout-form">
                                @csrf
                                <button type="submit" class="dash-topbar-logout" title="Sign out">Logout</button>
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
