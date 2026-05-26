@php
    $number = $number ?? '1';
    $title = $title ?? '';
    $description = $description ?? null;
    $id = $id ?? null;
@endphp

<section @if ($id) id="{{ $id }}" @endif class="dash-form-section">
    <header class="dash-form-section__head">
        <span class="dash-form-section__number" aria-hidden="true">{{ $number }}</span>
        <div class="dash-form-section__titles">
            <h2 class="dash-form-section-title">{{ $title }}</h2>
            @if ($description)
                <p class="dash-form-section-hint">{{ $description }}</p>
            @endif
        </div>
    </header>
    <div class="dash-form-section__body">
        {{ $slot }}
    </div>
</section>
