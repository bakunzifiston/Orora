@extends('layouts.health-module')

@section('title', 'Health — Disease')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Disease records',
        'subtitle' => 'Diagnosis history, severity, and recovery tracking.',
        'createRoute' => 'health.disease.create',
        'createLabel' => '+ Add disease record',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($diseaseRecords->isEmpty())
            <p class="dash-empty">No disease records yet. <a href="{{ route('health.disease.create') }}">Add disease record</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Animal</th>
                            <th>Farm</th>
                            <th>Disease</th>
                            <th>Severity</th>
                            <th>Recovery</th>
                            <th>Contagious</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($diseaseRecords as $record)
                            <tr>
                                <td><strong>{{ $record->disease_code }}</strong></td>
                                <td>{{ $record->diagnosis_date->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $record->animal->tag_number }}</strong>
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $record->animal->name }}</div>
                                </td>
                                <td>{{ $record->farm->name }}</td>
                                <td><strong>{{ $record->disease_name }}</strong></td>
                                <td><span class="dash-badge">{{ $record->severityLabel() }}</span></td>
                                <td><span class="dash-badge">{{ $record->recoveryLabel() }}</span></td>
                                <td>
                                    {{ $record->contagiousLabel() }}
                                    @if ($record->quarantine_required)
                                        <div style="font-size: 0.75rem; color: #b45309;">Quarantine</div>
                                    @endif
                                </td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $record,
                                        'editRoute' => 'health.disease.edit',
                                        'destroyRoute' => 'health.disease.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $diseaseRecords->links() }}</div>
        @endif
    </div>
@endsection
