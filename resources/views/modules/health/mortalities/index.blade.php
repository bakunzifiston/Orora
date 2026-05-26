@extends('layouts.health-module')

@section('title', 'Health — Mortality')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Mortality',
        'subtitle' => 'Death records and animals marked as deceased.',
        'createRoute' => 'health.mortalities.create',
        'createLabel' => '+ Add mortality record',
    ])
    @include('modules.partials.flash')

    @if ($deceasedAnimals->isNotEmpty())
        <div class="dash-panel" style="margin-bottom: 1rem;">
            <div class="dash-panel-title">Deceased animals</div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Name</th>
                            <th>Farm</th>
                            <th>Lifecycle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($deceasedAnimals as $animal)
                            <tr>
                                <td><strong>{{ $animal->tag_number }}</strong></td>
                                <td>{{ $animal->name }}</td>
                                <td>{{ $animal->farm->name }}</td>
                                <td><span class="dash-badge">{{ $animal->lifecycle_status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="dash-panel">
        <div class="dash-panel-title">Mortality records</div>
        @if ($mortalities->isEmpty())
            <p class="dash-empty" style="padding: 1rem 1.25rem;">No mortality records logged yet. <a href="{{ route('health.mortalities.create') }}">Add mortality record</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Death date</th>
                            <th>Animal</th>
                            <th>Cause</th>
                            <th>Reported by</th>
                            <th>Disposal</th>
                            <th>Postmortem</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mortalities as $mortality)
                            <tr>
                                <td>{{ $mortality->death_date->format('M j, Y') }}</td>
                                <td>
                                    <strong>{{ $mortality->animal->tag_number }}</strong>
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $mortality->animal->name }}</div>
                                </td>
                                <td>{{ $mortality->cause_of_death ?? '—' }}</td>
                                <td>{{ $mortality->reported_by ?? '—' }}</td>
                                <td>{{ $mortality->disposal_method ?? '—' }}</td>
                                <td>{{ $mortality->postmortem_done ? 'Yes' : 'No' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $mortality,
                                        'editRoute' => 'health.mortalities.edit',
                                        'destroyRoute' => 'health.mortalities.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $mortalities->links() }}</div>
        @endif
    </div>
@endsection
