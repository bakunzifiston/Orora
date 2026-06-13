<section class="ab-hero" style="--ab-hero-bg: url('{{ asset($about['hero']['background']) }}')">
    <div class="ab-hero__overlay"></div>
    <div class="mp-container ab-hero__inner">
        <h1 class="ab-hero__title">
            @foreach ($about['hero']['title'] as $line)
                <span>{{ $line }}</span>
            @endforeach
        </h1>
        <p class="ab-hero__subtitle">{{ $about['hero']['subtitle'] }}</p>
        <div class="ab-hero__actions">
            <a href="{{ route($about['hero']['primary_cta']['route']) }}" class="ab-btn ab-btn--primary ab-btn--lg">
                {{ $about['hero']['primary_cta']['label'] }}
            </a>
            <a href="{{ route($about['hero']['secondary_cta']['route']) }}" class="ab-btn ab-btn--outline-light ab-btn--lg">
                {{ $about['hero']['secondary_cta']['label'] }}
            </a>
        </div>
    </div>
</section>
