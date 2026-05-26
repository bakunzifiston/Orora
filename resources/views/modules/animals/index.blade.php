@extends('layouts.dashboard')

@section('title', 'Animals')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Animals',
        'subtitle' => 'Individual animals with identity, physical profile, and status tracking.',
        'createRoute' => 'animals.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($animals->isEmpty())
            <p class="dash-empty">No animals registered. <a href="{{ route('animals.create') }}">Register an animal</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th>Name</th>
                            <th>Livestock group</th>
                            <th>Gender</th>
                            <th>Health</th>
                            <th>Lifecycle</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($animals as $animal)
                            <tr>
                                <td><strong>{{ $animal->tag_number }}</strong></td>
                                <td>{{ $animal->name }}</td>
                                <td class="dash-table-cell-wrap">{{ $animal->livestock?->herd_groups_label ?? '—' }}</td>
                                <td>{{ $animal->gender_label }}</td>
                                <td><span class="dash-badge">{{ $animal->health_status }}</span></td>
                                <td><span class="dash-badge">{{ $animal->lifecycle_status }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $animal,
                                        'editRoute' => 'animals.edit',
                                        'destroyRoute' => 'animals.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $animals->links() }}</div>
        @endif
    </div>
@endsection
