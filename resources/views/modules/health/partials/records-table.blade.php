@php
    $section = $section ?? 'overview';
    $emptyMessage = $emptyMessage ?? 'No records in this section yet.';
    $createType = $createType ?? null;
@endphp

@if ($healthRecords->isEmpty())
    <p class="dash-empty">
        {{ $emptyMessage }}
        <a href="{{ route('health.records.create', array_filter(['type' => $createType, 'section' => $section])) }}">Log a record</a>.
    </p>
@else
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Animal</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Summary</th>
                    <th>Next follow-up</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($healthRecords as $record)
                    <tr>
                        <td>{{ $record->recorded_on->format('M j, Y') }}</td>
                        <td>
                            <strong>{{ $record->animal->tag_number }}</strong>
                            <div style="font-size: 0.75rem; color: #808080;">{{ $record->animal->name }}</div>
                        </td>
                        <td>{{ $record->record_type }}</td>
                        <td><span class="dash-badge">{{ $record->health_status }}</span></td>
                        <td class="dash-table-cell-wrap">{{ $record->title ?? $record->treatment ?? '—' }}</td>
                        <td>{{ $record->next_follow_up?->format('M j, Y') ?? '—' }}</td>
                        <td>
                            @include('modules.partials.row-actions', [
                                'model' => $record,
                                'editRoute' => 'health.records.edit',
                                'destroyRoute' => 'health.records.destroy',
                                'section' => $section,
                            ])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="dash-pagination">{{ $healthRecords->links() }}</div>
@endif
