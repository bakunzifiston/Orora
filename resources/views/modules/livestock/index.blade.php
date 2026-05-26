@extends('layouts.dashboard')

@section('title', 'Livestock')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Livestock',
        'subtitle' => 'Herd and flock groups by farm with production and feeding details.',
        'createRoute' => 'livestock.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($livestock->isEmpty())
            <p class="dash-empty">No livestock groups yet. <a href="{{ route('livestock.create') }}">Add livestock</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Group / herd</th>
                            <th>Farm</th>
                            <th>Types</th>
                            <th>Production</th>
                            <th>Head count</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($livestock as $group)
                            <tr>
                                <td class="dash-table-cell-wrap"><strong>{{ $group->herd_groups_label }}</strong></td>
                                <td>{{ $group->farm->name }}</td>
                                <td class="dash-table-cell-wrap">{{ $group->livestock_types_label }}</td>
                                <td class="dash-table-cell-wrap">{{ $group->production_purposes_label }}</td>
                                <td>{{ number_format($group->head_count) }}</td>
                                <td><span class="dash-badge">{{ ucfirst($group->status) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $group,
                                        'editRoute' => 'livestock.edit',
                                        'destroyRoute' => 'livestock.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $livestock->links() }}</div>
        @endif
    </div>
@endsection
