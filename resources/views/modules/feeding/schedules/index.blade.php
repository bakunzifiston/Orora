@extends('layouts.feeding-module')

@section('title', 'Feeding — Schedules')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Schedules',
        'subtitle' => 'Planned feeding routines linked to inventory stock.',
        'createRoute' => 'feeding.schedules.create',
        'createLabel' => '+ Add schedule',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($schedules->isEmpty())
            <p class="dash-empty">No schedules yet. <a href="{{ route('feeding.schedules.create') }}">Add schedule</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Feed type</th>
                            <th>Quantity</th>
                            <th>Frequency</th>
                            <th>Next due</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schedules as $schedule)
                            <tr>
                                <td>{{ $schedule->farm->name }}</td>
                                <td><strong>{{ $schedule->feedType->name }}</strong></td>
                                <td>{{ $schedule->quantity }} {{ $schedule->unit }}</td>
                                <td>{{ ucfirst($schedule->frequency) }}</td>
                                <td>{{ $schedule->next_due_date?->format('M j, Y') ?? '—' }}</td>
                                <td><span class="dash-badge">{{ ucfirst($schedule->status) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $schedule,
                                        'editRoute' => 'feeding.schedules.edit',
                                        'destroyRoute' => 'feeding.schedules.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $schedules->links() }}</div>
        @endif
    </div>
@endsection
