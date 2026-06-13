@php
    $logoPath = config('branding.logo');
    $logoFile = public_path(ltrim($logoPath, '/'));
    $logoUrl = asset($logoPath);
    $fallbackUrl = asset(config('branding.logo_sidebar'));
    $logoClass = $class ?? '';
    $useFallback = $useFallback ?? true;

    if (is_file($logoFile)) {
        $logoUrl .= '?v='.filemtime($logoFile);
    }
@endphp

<img
    src="{{ $logoUrl }}"
    alt="{{ $alt ?? 'Orora' }}"
    class="{{ $logoClass }}"
    @if ($useFallback)
        onerror="this.onerror=null;this.src='{{ $fallbackUrl }}';"
    @endif
>
