@extends('layouts.health-module')

@section('title', 'Health — Vaccinations')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Vaccinations',
        'subtitle' => 'Vaccination schedules, records, and due dates.',
        'createRoute' => 'health.vaccinations.create',
        'createLabel' => '+ Add vaccination',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($vaccinations->isEmpty())
            <p class="dash-empty">No vaccinations logged yet. <a href="{{ route('health.vaccinations.create') }}">Add vaccination</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Animal</th>
                            <th>Vaccine</th>
                            <th>Batch</th>
                            <th>Status</th>
                            <th>Next due</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vaccinations as $vaccination)
                            <tr>
                                <td>{{ $vaccination->vaccination_date->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $vaccination->animal->tag_number }}</strong>
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $vaccination->animal->name }}</div>
                                </td>
                                <td>
                                    <strong>{{ $vaccination->vaccine_name }}</strong>
                                    @if ($vaccination->vaccine_type)
                                        <div style="font-size: 0.75rem; color: #808080;">{{ $vaccination->vaccine_type }}</div>
                                    @endif
                                </td>
                                <td>{{ $vaccination->batch_number ?? '—' }}</td>
                                <td><span class="dash-badge">{{ $vaccination->status }}</span></td>
                                <td>{{ $vaccination->next_due_date?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $vaccination,
                                        'editRoute' => 'health.vaccinations.edit',
                                        'destroyRoute' => 'health.vaccinations.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $vaccinations->links() }}</div>
        @endif
    </div>
@endsection
