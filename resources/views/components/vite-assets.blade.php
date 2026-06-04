@props([
    'entries' => [],
    'fallback' => null,
])

@if (file_exists(public_path('build/manifest.json')))
    @vite($entries)
@elseif ($fallback === 'auth')
    @include('layouts.partials.auth-fallback-styles')
@elseif ($fallback === 'dashboard')
    @include('layouts.partials.dashboard-styles')
@endif
