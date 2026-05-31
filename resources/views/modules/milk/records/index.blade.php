@extends('layouts.milk-module')

@section('title', 'Milk — Records')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milk records',
        'subtitle' => 'Daily milk production per animal.',
        'createRoute' => 'milk.records.create',
        'createLabel' => '+ Record milk',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($milkRecords->isEmpty())
            <p class="dash-empty">No milk records yet. <a href="{{ route('milk.records.create') }}">Record milk</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Animal</th>
                            <th>Farm</th>
                            <th>Session</th>
                            <th>Quantity</th>
                            <th>Quality</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($milkRecords as $record)
                            <tr>
                                <td>{{ $record->recorded_on->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $record->animal->tag_number }}</strong>
                                    @if ($record->animal->name)
                                        <div style="font-size: 0.75rem; color: #808080;">{{ $record->animal->name }}</div>
                                    @endif
                                </td>
                                <td>{{ $record->farm->name }}</td>
                                <td>{{ $record->session ?? '—' }}</td>
                                <td><strong>{{ $record->quantity }} {{ $record->unit }}</strong></td>
                                <td>
                                    @if ($record->quality_grade || $record->fat_percentage)
                                        {{ $record->quality_grade ?? '' }}
                                        @if ($record->fat_percentage)
                                            <span style="font-size: 0.75rem; color: #808080;">{{ $record->fat_percentage }}% fat</span>
                                        @endif
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $record,
                                        'editRoute' => 'milk.records.edit',
                                        'destroyRoute' => 'milk.records.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $milkRecords->links() }}</div>
        @endif
    </div>
@endsection
