<div class="dash-feed-calc">
    @if (! empty($result['warnings']))
        <div class="dash-panel dash-feed-calc__warning">
            @foreach ($result['warnings'] as $warning)
                <p>{{ $warning }}</p>
            @endforeach
        </div>
    @endif

    <div class="dash-panel">
        <div class="dash-panel-title">Animal details</div>
        <dl class="dash-feed-calc__details">
            <div><dt>Type</dt><dd>{{ ucfirst($result['animal_type']) }}</dd></div>
            <div><dt>Weight</dt><dd>{{ $result['weight_kg'] > 0 ? number_format($result['weight_kg'], 1).' kg' : 'Not recorded' }}</dd></div>
            <div><dt>Production status</dt><dd>{{ $result['production_status'] ?: '—' }}</dd></div>
            <div><dt>Age</dt><dd>{{ number_format($result['age_months'], 0) }} months</dd></div>
            @if ($result['farm_name'])
                <div><dt>Farm</dt><dd>{{ $result['farm_name'] }}</dd></div>
            @endif
        </dl>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">Daily feed recommendation</div>
        <div class="dash-feed-calc__total">
            <span class="dash-feed-calc__total-label">Total daily feed</span>
            <strong class="dash-feed-calc__total-value">{{ number_format($result['total_feed_kg'], 2) }} kg / day</strong>
        </div>

        @include('modules.feeding.calculator._breakdown-bars', ['result' => $result])

        @if (! empty($result['basis']))
            <p class="dash-feed-calc__basis">{{ $result['basis'] }}</p>
        @endif
    </div>

    @include('modules.feeding.calculator._rule-explanation')
</div>
