@extends('layouts.dashboard')

@section('title', 'Farms')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Farms',
        'subtitle' => 'Registered farms with Rwanda location and owner details.',
        'createRoute' => 'farms.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($farms->isEmpty())
            <p class="dash-empty">No farms registered yet. <a href="{{ route('farms.create') }}">Register your first farm</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Reg. number</th>
                            <th>Location</th>
                            <th>Size (ha)</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($farms as $farm)
                            <tr>
                                <td><strong>{{ $farm->name }}</strong></td>
                                <td>{{ $farm->registration_number ?? '—' }}</td>
                                <td>{{ $farm->location_label ?: '—' }}</td>
                                <td>{{ $farm->farm_size_hectares !== null ? number_format($farm->farm_size_hectares, 2) : '—' }}</td>
                                <td>{{ $farm->owner_full_name ?: '—' }}</td>
                                <td><span class="dash-badge">{{ ucfirst($farm->status) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $farm,
                                        'editRoute' => 'farms.edit',
                                        'destroyRoute' => 'farms.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $farms->links() }}</div>
        @endif
    </div>
@endsection
