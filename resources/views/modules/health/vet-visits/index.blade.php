@extends('layouts.health-module')

@section('title', 'Health — Vet visits')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Vet visits',
        'subtitle' => 'Veterinary checkups and consultations.',
        'createRoute' => 'health.vet-visits.create',
        'createLabel' => '+ Add vet visit',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($vetVisits->isEmpty())
            <p class="dash-empty">No vet visits logged yet. <a href="{{ route('health.vet-visits.create') }}">Add vet visit</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>Animal</th>
                            <th>Disease</th>
                            <th>Medicine</th>
                            <th>Veterinarian</th>
                            <th>Status</th>
                            <th>Follow-up</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vetVisits as $vetVisit)
                            <tr>
                                <td>{{ $vetVisit->start_date->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $vetVisit->animal->tag_number }}</strong>
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $vetVisit->animal->name }}</div>
                                </td>
                                <td><strong>{{ $vetVisit->disease_name }}</strong></td>
                                <td>
                                    <strong>{{ $vetVisit->medicine_name }}</strong>
                                    @if ($vetVisit->dosage)
                                        <div style="font-size: 0.75rem; color: #808080;">{{ $vetVisit->dosage }}</div>
                                    @endif
                                </td>
                                <td>{{ $vetVisit->veterinarian_name ?? '—' }}</td>
                                <td><span class="dash-badge">{{ $vetVisit->status }}</span></td>
                                <td>{{ $vetVisit->follow_up_date?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $vetVisit,
                                        'editRoute' => 'health.vet-visits.edit',
                                        'destroyRoute' => 'health.vet-visits.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $vetVisits->links() }}</div>
        @endif
    </div>
@endsection
