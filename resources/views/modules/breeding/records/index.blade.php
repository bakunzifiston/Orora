@extends('layouts.breeding-module')

@section('title', 'Breeding — Records')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Breeding records',
        'subtitle' => 'Mating events — natural or AI.',
        'createRoute' => 'breeding.records.create',
        'createLabel' => '+ Record breeding',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel" style="margin-bottom: 1rem;">
        <form method="GET" class="dash-form-grid" style="padding: 1rem 1.25rem;">
            <div class="dash-form-field">
                <label for="farm_id">Farm</label>
                <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                    <option value="">All farms</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected(request('farm_id') == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="status">Status</label>
                <select name="status" id="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (config('modules.breeding_statuses') as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ config('modules.breeding_status_labels')[$status] }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="dash-panel">
        @if ($records->isEmpty())
            <p class="dash-empty">No breeding records yet. <a href="{{ route('breeding.records.create') }}">Record breeding</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Female</th>
                            <th>Sire</th>
                            <th>Type</th>
                            <th>Expected calving</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td><strong>{{ $record->breeding_code }}</strong></td>
                                <td>{{ $record->breeding_date->format('M j, Y') }}</td>
                                <td>{{ $record->femaleAnimal->tag_number }}</td>
                                <td>{{ $record->sireLabel() }}</td>
                                <td>{{ $record->breedingTypeLabel() }}</td>
                                <td>{{ $record->expected_calving_date?->format('M j, Y') ?? '—' }}</td>
                                <td>{{ $record->statusLabel() }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $record,
                                        'editRoute' => 'breeding.records.edit',
                                        'destroyRoute' => 'breeding.records.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
