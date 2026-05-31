<nav class="dash-health-subnav" aria-label="Breeding sections">
    @foreach ($breedingSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link @if(($activeBreedingSection ?? '') === $section['key']) is-active @endif"
        >{{ $section['label'] }}</a>
    @endforeach
</nav>
