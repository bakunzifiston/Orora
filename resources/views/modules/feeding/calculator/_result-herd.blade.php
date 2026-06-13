<div class="dash-feed-calc">
    @if (! empty($result['warnings']))
        <div class="dash-panel dash-feed-calc__warning">
            @foreach (array_unique($result['warnings']) as $warning)
                <p>{{ $warning }}</p>
            @endforeach
        </div>
    @endif

    <div class="dash-panel">
        <div class="dash-panel-title">Herd summary</div>
        <dl class="dash-feed-calc__details">
            <div><dt>Total animals</dt><dd>{{ number_format($result['animal_count']) }}</dd></div>
            <div><dt>Total daily feed</dt><dd><strong>{{ number_format($result['total_feed_kg'], 2) }} kg / day</strong></dd></div>
            <div><dt>Average per animal</dt><dd>{{ number_format($result['per_animal_avg'], 2) }} kg / day</dd></div>
            @if ($result['farm_name'])
                <div><dt>Farm</dt><dd>{{ $result['farm_name'] }}</dd></div>
            @endif
        </dl>

        @include('modules.feeding.calculator._breakdown-bars', ['result' => $result])
    </div>

    @include('modules.feeding.calculator._rule-explanation')

    <div class="dash-panel">
        <div class="dash-panel-title">Per animal breakdown</div>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Animal</th>
                        <th>Rule</th>
                        <th>Weight</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: right;">Roughage</th>
                        <th style="text-align: right;">Concentrate</th>
                        <th style="text-align: right;">Supplement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($result['breakdown'] as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['tag_number'] }}</strong>
                                @if ($row['animal_name'])
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $row['animal_name'] }}</div>
                                @endif
                            </td>
                            <td style="font-size: 0.8125rem;">{{ $row['rule_label'] }}</td>
                            <td>{{ $row['weight_kg'] ? number_format((float) $row['weight_kg'], 1).' kg' : '—' }}</td>
                            <td>{{ $row['production_status'] ?: '—' }}</td>
                            <td style="text-align: right;">{{ number_format($row['total_feed_kg'], 2) }} kg</td>
                            <td style="text-align: right;">{{ number_format($row['roughage_kg'], 2) }} kg</td>
                            <td style="text-align: right;">{{ number_format($row['concentrate_kg'], 2) }} kg</td>
                            <td style="text-align: right;">{{ number_format($row['supplement_kg'], 2) }} kg</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
