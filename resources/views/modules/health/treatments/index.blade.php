@extends('layouts.health-module')

@section('title', 'Health — Treatments')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Treatments',
        'subtitle' => 'Treatment history and medication logs.',
        'createRoute' => 'health.treatments.create',
        'createLabel' => '+ Add treatment',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($treatments->isEmpty())
            <p class="dash-empty">No treatments logged yet. <a href="{{ route('health.treatments.create') }}">Add treatment</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Start</th>
                            <th>Animal</th>
                            <th>Disease</th>
                            <th>Medicine</th>
                            <th>Status</th>
                            <th>Follow-up</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($treatments as $treatment)
                            <tr>
                                <td>{{ $treatment->start_date->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $treatment->animal->tag_number }}</strong>
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $treatment->animal->name }}</div>
                                </td>
                                <td><strong>{{ $treatment->disease_name }}</strong></td>
                                <td>
                                    <strong>{{ $treatment->medicine_name }}</strong>
                                    @if ($treatment->dosage)
                                        <div style="font-size: 0.75rem; color: #808080;">{{ $treatment->dosage }}</div>
                                    @endif
                                </td>
                                <td><span class="dash-badge">{{ $treatment->status }}</span></td>
                                <td>{{ $treatment->follow_up_date?->format('M j, Y') ?? '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $treatment,
                                        'editRoute' => 'health.treatments.edit',
                                        'destroyRoute' => 'health.treatments.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $treatments->links() }}</div>
        @endif
    </div>
@endsection
