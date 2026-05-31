<nav class="dash-health-subnav" aria-label="Sales sections">
    @foreach ($salesSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeSalesSection ?? '') === $section['key'] ? 'is-active' : '' }}"
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
