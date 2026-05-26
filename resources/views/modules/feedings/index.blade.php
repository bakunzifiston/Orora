@extends('layouts.feeding-module')

@section('title', 'Feeding — Records')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Feeding records',
        'subtitle' => 'Actual feed given — deducts from inventory automatically.',
        'createRoute' => 'feeding.records.create',
        'createLabel' => '+ Log feeding',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($feedings->isEmpty())
            <p class="dash-empty">No feeding records. <a href="{{ route('feeding.records.create') }}">Log feeding</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Farm</th>
                            <th>Feed type</th>
                            <th>Quantity</th>
                            <th>Animal / Group</th>
                            <th>Schedule</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedings as $feeding)
                            <tr>
                                <td>{{ $feeding->fed_on->format('M j, Y') }}</td>
                                <td>{{ $feeding->farm->name }}</td>
                                <td><strong>{{ $feeding->feedType?->name }}</strong></td>
                                <td>{{ $feeding->quantity }} {{ $feeding->unit }}</td>
                                <td>
                                    @if ($feeding->animal)
                                        {{ $feeding->animal->tag_number }}
                                    @elseif ($feeding->livestock)
                                        {{ $feeding->livestock->name }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $feeding->feedingSchedule ? 'Yes' : '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $feeding,
                                        'editRoute' => 'feeding.records.edit',
                                        'destroyRoute' => 'feeding.records.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $feedings->links() }}</div>
        @endif
    </div>
@endsection
