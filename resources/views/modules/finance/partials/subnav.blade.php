<nav class="dash-health-subnav" aria-label="Finance sections">
    @foreach ($financeSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeFinanceSection ?? '') === $section['key'] ? 'is-active' : '' }}"
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
