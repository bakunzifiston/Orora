@extends('layouts.milk-module')

@section('title', 'Milk — Storage')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milk storage',
        'subtitle' => 'Tanks and containers. Stock increases when sessions complete.',
        'createRoute' => 'milk.storage.create',
        'createLabel' => '+ Add container',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($storageUnits->isEmpty())
            <p class="dash-empty">No storage containers yet. <a href="{{ route('milk.storage.create') }}">Add one</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Container</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($storageUnits as $unit)
                            <tr>
                                <td>{{ $unit->farm->name }}</td>
                                <td><strong>{{ $unit->container_name }}</strong><div style="font-size:0.75rem;color:#808080;">{{ $unit->storage_code }}</div></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $unit->container_type)) }}</td>
                                <td>
                                    <strong @if($unit->isLowCapacity()) style="color:#b45309;" @endif>
                                        {{ number_format($unit->current_quantity_liters, 2) }} / {{ number_format($unit->capacity_liters, 2) }} L
                                    </strong>
                                </td>
                                <td><span class="dash-badge">{{ ucfirst(str_replace('_', ' ', $unit->status)) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $unit,
                                        'editRoute' => 'milk.storage.edit',
                                        'destroyRoute' => 'milk.storage.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $storageUnits->links() }}</div>
        @endif
    </div>
@endsection
