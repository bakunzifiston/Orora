@php
    $rules = config('feed_calculator');
    $methods = $rules['calculation_methods'] ?? [];
    $types = $rules['animal_types'] ?? [];
@endphp

<details class="dash-panel dash-feed-calc__reference">
    <summary class="dash-feed-calc__reference-summary">
        <span class="dash-panel-title" style="margin: 0;">Feed requirement guidelines</span>
        <span class="dash-feed-calc__reference-hint">How recommendations are calculated</span>
    </summary>

    <div class="dash-feed-calc__reference-body">
        <p class="dash-feed-calc__reference-intro">
            Orora applies the standard rules below. Your animal’s <strong>species</strong>, <strong>weight</strong>,
            <strong>production status</strong>, and <strong>age</strong> determine which row is used.
            Lactating cattle also use recent <strong>milk yield</strong> from the milk module when available.
        </p>

        <div class="dash-feed-calc__methods">
            @foreach ($methods as $method)
                <div class="dash-feed-calc__method-card">
                    <strong>{{ $method['label'] }}</strong>
                    <code>{{ $method['formula'] }}</code>
                    <span>{{ $method['breakdown'] }}</span>
                </div>
            @endforeach
        </div>

        <dl class="dash-feed-calc__input-sources">
            <dt>Data we read</dt>
            @foreach ($rules['input_sources'] ?? [] as $field => $description)
                <dd><strong>{{ str_replace('_', ' ', $field) }}:</strong> {{ $description }}</dd>
            @endforeach
        </dl>

        @foreach ($types as $typeKey => $type)
            <div class="dash-feed-calc__type-block">
                <h3>{{ $type['label'] }}</h3>
                <p class="dash-feed-calc__type-method">
                    Method: {{ $methods[$type['method']]['label'] ?? $type['method'] }}
                </p>

                @if ($typeKey === 'cattle')
                    <p class="dash-feed-calc__type-note">Lactating cattle use milk-yield tiers instead of a single row:</p>
                    <div class="dash-table-wrap">
                        <table class="dash-table dash-table--compact">
                            <thead>
                                <tr>
                                    <th>Tier</th>
                                    <th>DM %</th>
                                    <th>Roughage</th>
                                    <th>Concentrate</th>
                                    <th>Supplement</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rules['lactating_cattle_tiers'] ?? [] as $tier)
                                    <tr>
                                        <td>{{ $tier['label'] }}</td>
                                        <td>{{ $tier['dm_percent'] }}%</td>
                                        <td>{{ $tier['roughage_pct'] }}%</td>
                                        <td>{{ $tier['concentrate_pct'] }}%</td>
                                        <td>{{ $tier['supplement_pct'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="dash-table-wrap">
                    <table class="dash-table dash-table--compact">
                        <thead>
                            <tr>
                                <th>Production status</th>
                                @if (($type['method'] ?? '') === 'weight_based')
                                    <th>DM % BW</th>
                                @else
                                    <th>Daily amount</th>
                                @endif
                                <th>Roughage</th>
                                <th>Concentrate</th>
                                <th>Supplement</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($type['rules'] as $rule)
                                <tr>
                                    <td>{{ $rule['label'] }}</td>
                                    <td>
                                        @if (isset($rule['fixed_grams']))
                                            {{ $rule['fixed_grams'] }} g
                                        @elseif (isset($rule['fixed_kg']))
                                            {{ $rule['fixed_kg'] }} kg
                                        @else
                                            {{ $rule['dm_percent'] }}%
                                        @endif
                                    </td>
                                    <td>{{ $rule['roughage_pct'] }}%</td>
                                    <td>{{ $rule['concentrate_pct'] }}%</td>
                                    <td>{{ $rule['supplement_pct'] }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</details>
