<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Orora Farm — modern livestock and farm management for Rwanda and Africa. Register free and manage animals, milk, health, sales, and finance in one system.')">
    <title>@yield('title', config('marketplace.site_name')) — Orora Farm</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/marketplace-landing.css', 'resources/css/marketplace-shop.css', 'resources/css/marketplace-learning.css', 'resources/css/marketplace-about.css', 'resources/css/marketplace-contact.css', 'resources/css/marketplace-trace.css', 'resources/js/app.js', 'resources/js/landing-page.js'])
    @stack('scripts')
</head>
<body class="mp-body {{ ($activePage ?? '') === 'home' ? 'mp-body--home' : '' }} {{ ($activePage ?? '') === 'shop' ? 'mp-body--shop' : '' }}">
    @include('marketplace.partials.navbar', ['activePage' => $activePage ?? ''])

    <main>
        @yield('content')
    </main>

    @include('marketplace.partials.footer')
    @stack('body-scripts')
</body>
</html>
