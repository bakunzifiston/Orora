@extends('layouts.dashboard')

@section('title', 'Movement')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Movement',
        'subtitle' => 'Transfers and relocations between farms.',
        'createRoute' => 'movements.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($movements->isEmpty())
            <p class="dash-empty">No movement records. <a href="{{ route('movements.create') }}">Record movement</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Animal</th>
                            <th>Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($movements as $movement)
                            <tr>
                                <td>{{ $movement->moved_on->format('M j, Y') }}</td>
                                <td><strong>{{ $movement->animal->tag_number }}</strong></td>
                                <td>{{ ucfirst($movement->movement_type) }}</td>
                                <td>{{ $movement->fromFarm->name }}</td>
                                <td>{{ $movement->toFarm?->name ?? '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $movement,
                                        'editRoute' => 'movements.edit',
                                        'destroyRoute' => 'movements.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $movements->links() }}</div>
        @endif
    </div>
@endsection
