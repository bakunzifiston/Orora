<section class="tr-block">
    <h2 class="tr-block__title">Animal Identity</h2>
    <div class="tr-identity">
        <div class="tr-identity__photo">
            @if ($animal->photo_url)
                <img src="{{ $animal->photo_url }}" alt="{{ $animal->name ?: $animal->tag_number }}" class="tr-identity__image">
            @else
                @php
                    $initials = collect(preg_split('/\s+/', trim($animal->name ?: '')) ?: [])
                        ->filter()
                        ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
                        ->take(2)
                        ->join('') ?: strtoupper(substr($animal->tag_number, 0, 2));
                @endphp
                <div class="tr-identity__placeholder" aria-hidden="true">{{ $initials }}</div>
            @endif
        </div>
        <dl class="tr-identity__details">
            <div class="tr-identity__row">
                <dt>Animal Code</dt>
                <dd>{{ $animalCode }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Tag Number</dt>
                <dd>{{ $animal->tag_number }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Name</dt>
                <dd>{{ $animal->name ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Gender</dt>
                <dd>{{ $animal->gender_label }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Type</dt>
                <dd>{{ $animal->species ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Breed</dt>
                <dd>{{ $animal->breed ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Birth Date</dt>
                <dd>{{ $animal->date_of_birth?->format('M j, Y') ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Age</dt>
                <dd>{{ $animal->age_label ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Weight</dt>
                <dd>{{ $animal->weight_kg !== null ? number_format($animal->weight_kg, 0).' kg' : '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Color</dt>
                <dd>{{ $animal->color_markings ?: '—' }}</dd>
            </div>
            <div class="tr-identity__row">
                <dt>Status</dt>
                <dd>
                    <span class="tr-status tr-status--active">
                        <span class="tr-status__dot" aria-hidden="true"></span>
                        {{ $animal->lifecycle_status }}
                    </span>
                </dd>
            </div>
        </dl>
    </div>
</section>
