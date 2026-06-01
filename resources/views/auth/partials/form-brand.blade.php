@php
    $authLogoMain = asset(config('branding.logo'));
    $authLogoSidebar = asset(config('branding.logo_sidebar'));
@endphp

<div class="auth-form-brand">
    <img
        src="{{ $authLogoMain }}"
        alt="Orora"
        class="auth-form-brand__logo"
        onerror="this.onerror=null;this.src='{{ $authLogoSidebar }}';"
    >
</div>
