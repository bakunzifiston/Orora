<nav class="dash-health-subnav dash-health-subnav--buttons" aria-label="Health sections">
    @foreach ($healthSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeHealthSection ?? '') === $section['key'] ? 'is-active' : '' }}"
            @if (($activeHealthSection ?? '') === $section['key']) aria-current="page" @endif
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
