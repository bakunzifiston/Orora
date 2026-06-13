<nav class="dash-health-subnav" aria-label="Health sections">
    @foreach ($healthSections as $section)
        @if (! empty($section['route']) && Route::has($section['route']))
            <a
                href="{{ route($section['route']) }}"
                class="dash-health-subnav__link {{ ($activeHealthSection ?? '') === $section['key'] ? 'is-active' : '' }}"
            >
                {{ $section['label'] }}
            </a>
        @endif
    @endforeach
</nav>
