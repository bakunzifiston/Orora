<nav class="dash-health-subnav" aria-label="Customer sections">
    @foreach ($customerSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeCustomerSection ?? '') === $section['key'] ? 'is-active' : '' }}"
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
