@php
    $segments = [
        ['key' => 'roughage', 'label' => 'Roughage', 'kg' => $result['roughage_kg'], 'pct' => $result['roughage_pct'], 'color' => '#A4D400'],
        ['key' => 'concentrate', 'label' => 'Concentrate', 'kg' => $result['concentrate_kg'], 'pct' => $result['concentrate_pct'], 'color' => '#002B2B'],
        ['key' => 'supplement', 'label' => 'Supplement', 'kg' => $result['supplement_kg'], 'pct' => $result['supplement_pct'], 'color' => '#60a5fa'],
    ];
@endphp

<div class="dash-feed-calc__breakdown">
    <div class="dash-feed-calc__stack" aria-hidden="true">
        @foreach ($segments as $segment)
            @if ($segment['pct'] > 0)
                <span style="flex: {{ $segment['pct'] }}; background: {{ $segment['color'] }};"></span>
            @endif
        @endforeach
    </div>
    <ul class="dash-feed-calc__legend">
        @foreach ($segments as $segment)
            <li>
                <span class="dash-feed-calc__swatch" style="background: {{ $segment['color'] }};"></span>
                <span class="dash-feed-calc__legend-label">{{ $segment['label'] }}</span>
                <span class="dash-feed-calc__legend-value">{{ number_format($segment['kg'], 2) }} kg ({{ $segment['pct'] }}%)</span>
            </li>
        @endforeach
    </ul>
</div>
