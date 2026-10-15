<nav class="dash-health-subnav dash-health-subnav--buttons" aria-label="Feeding sections">
    @foreach ($feedingSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeFeedingSection ?? '') === $section['key'] ? 'is-active' : '' }}"
            @if (($activeFeedingSection ?? '') === $section['key']) aria-current="page" @endif
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
