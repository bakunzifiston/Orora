@extends('layouts.health-module')

@section('title', 'Health — Timeline')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Timeline',
        'subtitle' => 'Chronological view of all health events.',
        'createRoute' => 'health.records.create',
        'createRouteParams' => ['section' => 'timeline'],
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($healthRecords->isEmpty())
            <p class="dash-empty">No health events yet. <a href="{{ route('health.records.create', ['section' => 'timeline']) }}">Log a record</a>.</p>
        @else
            <div class="dash-health-timeline">
                @foreach ($healthRecords as $record)
                    <article class="dash-health-timeline__item">
                        <div class="dash-health-timeline__date">
                            <strong>{{ $record->recorded_on->format('M j, Y') }}</strong>
                            <span>{{ $record->recorded_on->format('D') }}</span>
                        </div>
                        <div class="dash-health-timeline__body">
                            <div class="dash-health-timeline__meta">
                                <span class="dash-badge">{{ $record->record_type }}</span>
                                <span class="dash-badge">{{ $record->health_status }}</span>
                            </div>
                            <h3>{{ $record->animal->tag_number }} — {{ $record->animal->name }}</h3>
                            <p style="color: #808080; font-size: 0.8125rem; margin: 0 0 0.5rem;">{{ $record->farm->name }}</p>
                            @if ($record->title)
                                <p style="margin: 0 0 0.35rem;"><strong>{{ $record->title }}</strong></p>
                            @endif
                            @if ($record->treatment || $record->medication)
                                <p style="margin: 0; font-size: 0.875rem;">
                                    @if ($record->treatment) Treatment: {{ $record->treatment }} @endif
                                    @if ($record->medication) · Meds: {{ $record->medication }} @endif
                                </p>
                            @endif
                            <div class="dash-table-actions" style="margin-top: 0.75rem; justify-content: flex-start;">
                                @include('modules.partials.row-actions', [
                                    'model' => $record,
                                    'editRoute' => 'health.records.edit',
                                    'destroyRoute' => 'health.records.destroy',
                                    'section' => 'timeline',
                                ])
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="dash-pagination">{{ $healthRecords->links() }}</div>
        @endif
    </div>
@endsection
