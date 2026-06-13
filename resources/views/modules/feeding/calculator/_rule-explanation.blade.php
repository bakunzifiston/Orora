@if (! empty($result['explanation']))
    @php $explanation = $result['explanation']; @endphp
    <div class="dash-panel dash-feed-calc__explain">
        <div class="dash-panel-title">How this recommendation was calculated</div>

        <dl class="dash-feed-calc__explain-grid">
            <div>
                <dt>Matched rule</dt>
                <dd><strong>{{ $explanation['rule_label'] }}</strong></dd>
            </div>
            <div>
                <dt>Calculation method</dt>
                <dd>{{ $explanation['method_label'] }}</dd>
            </div>
            <div>
                <dt>Animal type used</dt>
                <dd>{{ ucfirst($explanation['inputs']['animal_type'] ?? '—') }}</dd>
            </div>
            <div>
                <dt>Status used</dt>
                <dd>
                    {{ $explanation['inputs']['production_status'] ?: '—' }}
                    @if (! empty($explanation['inputs']['normalized_status']) && ($explanation['inputs']['normalized_status'] !== strtolower($explanation['inputs']['production_status'] ?? '')))
                        <span class="dash-feed-calc__mapped">→ {{ $explanation['inputs']['normalized_status'] }}</span>
                    @endif
                </dd>
            </div>
            @if (isset($explanation['inputs']['age_months']))
                <div>
                    <dt>Age used</dt>
                    <dd>{{ number_format($explanation['inputs']['age_months'], 1) }} months</dd>
                </div>
            @endif
            @if (array_key_exists('weight_kg', $explanation['inputs']))
                <div>
                    <dt>Weight used</dt>
                    <dd>{{ $explanation['inputs']['weight_kg'] > 0 ? number_format($explanation['inputs']['weight_kg'], 1).' kg' : 'Not recorded' }}</dd>
                </div>
            @endif
            @if (! empty($explanation['inputs']['avg_milk_yield_liters']))
                <div>
                    <dt>Milk yield (avg)</dt>
                    <dd>{{ number_format($explanation['inputs']['avg_milk_yield_liters'], 2) }} L/day</dd>
                </div>
            @endif
        </dl>

        @if (! empty($explanation['parameters']))
            <div class="dash-feed-calc__params">
                <span class="dash-feed-calc__params-title">Rule parameters</span>
                <ul>
                    @foreach ($explanation['parameters'] as $label => $value)
                        <li><strong>{{ $label }}:</strong> {{ $value }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($explanation['steps']))
            <ol class="dash-feed-calc__steps">
                @foreach ($explanation['steps'] as $step)
                    <li>{{ $step }}</li>
                @endforeach
            </ol>
        @endif
    </div>
@endif
