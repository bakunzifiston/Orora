@extends('layouts.feeding-module')

@section('title', 'Feeding — Inventory')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Inventory',
        'subtitle' => 'Stock per farm and feed type. Movements update quantity on hand.',
        'createRoute' => 'feeding.inventory.create',
        'createLabel' => '+ Add inventory',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($inventories->isEmpty())
            <p class="dash-empty">No inventory items yet. <a href="{{ route('feeding.inventory.create') }}">Add inventory</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Farm</th>
                            <th>Feed type</th>
                            <th>Supplier</th>
                            <th>On hand</th>
                            <th>Reorder</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventories as $inventory)
                            <tr>
                                <td>{{ $inventory->farm->name }}</td>
                                <td><strong>{{ $inventory->feedType->name }}</strong></td>
                                <td>{{ $inventory->feedType->supplier?->name ?? '—' }}</td>
                                <td>
                                    <strong @if($inventory->isLowStock()) style="color: #b45309;" @endif>
                                        {{ $inventory->quantity_on_hand }} {{ $inventory->unit }}
                                    </strong>
                                </td>
                                <td>{{ $inventory->reorder_level ?? '—' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $inventory,
                                        'editRoute' => 'feeding.inventory.edit',
                                        'destroyRoute' => 'feeding.inventory.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $inventories->links() }}</div>
        @endif
    </div>
@endsection
