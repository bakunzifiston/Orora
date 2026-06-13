<section class="ab-section ab-stats" data-ab-stats>
    <div class="mp-container">
        <h2 class="ab-section__title ab-section__title--center">By the Numbers</h2>
        <div class="ab-stats__grid">
            @foreach ($stats as $stat)
                <article class="ab-stat">
                    @if ($stat['animate'] && $stat['value'] !== null)
                        <div
                            class="ab-stat__value"
                            data-count-up
                            data-target="{{ $stat['value'] }}"
                            data-suffix="{{ $stat['suffix'] }}"
                        >0{{ $stat['suffix'] }}</div>
                    @else
                        <div class="ab-stat__value">
                            {{ $stat['display'] ?? ($stat['value'].$stat['suffix']) }}
                        </div>
                    @endif
                    <div class="ab-stat__label">{{ $stat['label'] }}</div>
                </article>
            @endforeach
        </div>
    </div>
</section>
