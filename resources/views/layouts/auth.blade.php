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
<body class="auth-page">
    @php
        $authBgPath = public_path(ltrim(config('branding.auth_background'), '/'));
        $authBgUrl = asset(config('branding.auth_background'));
        if (is_file($authBgPath)) {
            $authBgUrl .= '?v='.filemtime($authBgPath);
        }
    @endphp
    <div class="auth-split">
        <section
            class="auth-visual"
            style="--auth-bg: url('{{ $authBgUrl }}');"
            aria-label="Orora farm management"
        >
            <div class="auth-visual__overlay" aria-hidden="true"></div>

            <div class="auth-visual__quote-wrap">
                <blockquote class="auth-visual__quote">
                    @yield('hero-quote', 'One platform for every part of your farm operation.')
                </blockquote>
                <cite class="auth-visual__cite">
                    @yield('hero-cite-name', 'Orora Farm')
                    <span class="auth-visual__cite-role">@yield('hero-cite-role', 'Smart farm management, simplified.')</span>
                </cite>
            </div>
        </section>

        <main class="auth-main">
            <div class="auth-main__shell">
                <div class="auth-main__content">
                    @include('auth.partials.form-brand')
                    @yield('content')
                </div>

                <footer class="auth-main__footer">
                    @yield('footer')
                </footer>
            </div>
        </main>
    </div>
</body>
</html>
