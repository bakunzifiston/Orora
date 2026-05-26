<nav class="dash-health-subnav" aria-label="Health sections">
    @foreach ($healthSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeHealthSection ?? '') === $section['key'] ? 'is-active' : '' }}"
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
