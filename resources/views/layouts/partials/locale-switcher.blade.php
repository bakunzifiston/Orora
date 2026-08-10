@php
    $locales = config('localization.supported', []);
    $current = app()->getLocale();
@endphp

<div class="dash-locale" aria-label="{{ __('Language') }}">
    @foreach ($locales as $code => $meta)
        <a
            href="{{ route('locale.switch', $code) }}"
            class="dash-locale__btn @if ($current === $code) is-active @endif"
            hreflang="{{ $code }}"
            lang="{{ $code }}"
            title="{{ __($meta['label']) }}"
        >{{ $meta['short'] }}</a>
    @endforeach
</div>
