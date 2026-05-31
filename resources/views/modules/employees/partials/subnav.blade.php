<nav class="dash-health-subnav" aria-label="Employee sections">
    @foreach ($employeeSections as $section)
        <a
            href="{{ route($section['route']) }}"
            class="dash-health-subnav__link {{ ($activeEmployeeSection ?? '') === $section['key'] ? 'is-active' : '' }}"
        >
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
