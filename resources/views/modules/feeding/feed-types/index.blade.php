@extends('layouts.feeding-module')

@section('title', 'Feeding — Feed types')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Feed types',
        'subtitle' => 'Products linked to suppliers, used in inventory and feeding records.',
        'createRoute' => 'feeding.feed-types.create',
        'createLabel' => '+ Add feed type',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($feedTypes->isEmpty())
            <p class="dash-empty">No feed types yet. <a href="{{ route('feeding.feed-types.create') }}">Add feed type</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Supplier</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Inventory</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedTypes as $feedType)
                            <tr>
                                <td><strong>{{ $feedType->name }}</strong></td>
                                <td>{{ $feedType->supplier?->name ?? '—' }}</td>
                                <td>{{ $feedType->category ?? '—' }}</td>
                                <td>{{ $feedType->unit }}</td>
                                <td>{{ $feedType->inventories_count }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $feedType,
                                        'editRoute' => 'feeding.feed-types.edit',
                                        'destroyRoute' => 'feeding.feed-types.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $feedTypes->links() }}</div>
        @endif
    </div>
@endsection
