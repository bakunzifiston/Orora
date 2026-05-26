@extends('layouts.feeding-module')

@section('title', 'Feeding — Suppliers')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Suppliers',
        'subtitle' => 'Feed suppliers at the start of the feeding chain.',
        'createRoute' => 'feeding.suppliers.create',
        'createLabel' => '+ Add supplier',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($suppliers->isEmpty())
            <p class="dash-empty">No suppliers yet. <a href="{{ route('feeding.suppliers.create') }}">Add supplier</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Feed types</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td><strong>{{ $supplier->name }}</strong></td>
                                <td>{{ $supplier->contact_person ?? '—' }}</td>
                                <td>{{ $supplier->phone ?? '—' }}</td>
                                <td>{{ $supplier->feed_types_count }}</td>
                                <td><span class="dash-badge">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $supplier,
                                        'editRoute' => 'feeding.suppliers.edit',
                                        'destroyRoute' => 'feeding.suppliers.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $suppliers->links() }}</div>
        @endif
    </div>
@endsection
