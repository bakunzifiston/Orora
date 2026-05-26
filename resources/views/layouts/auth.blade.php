<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — Orora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('layouts.partials.auth-fallback-styles')
</head>
<body class="auth-page min-h-screen font-sans antialiased">
    <div class="auth-shell flex min-h-screen flex-col lg:flex-row">
        {{-- Left panel — black + logo --}}
        <aside class="auth-sidebar relative flex flex-col justify-between overflow-hidden px-8 py-10 lg:w-[42%] lg:min-h-screen lg:px-12 lg:py-14">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="auth-shape auth-shape-1"></div>
                <div class="auth-shape auth-shape-2"></div>
                <div class="auth-shape auth-shape-3"></div>
                <div class="auth-shape auth-shape-4"></div>
            </div>

            <div class="relative z-10">
                <img
                    src="{{ asset(config('branding.logo')) }}"
                    alt="Orora Investment Group"
                    class="auth-logo w-full max-w-[220px]"
                >
            </div>

            <div class="relative z-10 my-10 lg:my-0">
                <h1 class="auth-heading text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-[2.5rem]">
                    @yield('sidebar-heading', 'Welcome to Orora')
                </h1>
                <p class="auth-muted mt-5 max-w-md text-base leading-relaxed sm:text-lg" style="color: #CCCCCC;">
                    @yield('sidebar-text', 'Your investment workspace — secure, modular, and built for growth.')
                </p>
            </div>

            <p class="relative z-10 text-xs uppercase tracking-widest" style="color: #808080;">
                &copy; {{ date('Y') }} Orora Investment Group
            </p>
        </aside>

        {{-- Right panel — white form area --}}
        <main class="auth-main flex flex-1 flex-col justify-center px-6 py-10 sm:px-12 lg:px-16 lg:py-14 xl:px-24">
            <div class="mx-auto w-full max-w-md">
                <img
                    src="{{ asset(config('branding.logo')) }}"
                    alt="Orora"
                    class="auth-logo mb-8 max-w-[180px] lg:hidden"
                >

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
