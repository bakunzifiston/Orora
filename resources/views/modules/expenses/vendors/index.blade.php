@extends('layouts.expenses-module')

@section('title', 'Expenses — Vendors')

@section('expense-content')
    @include('modules.partials.header', [
        'title' => 'Vendors',
        'subtitle' => 'Suppliers and service providers for expenses.',
        'createRoute' => 'expenses.vendors.create',
        'createLabel' => '+ Add vendor',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($vendors->isEmpty())
            <p class="dash-empty">No vendors yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Phone</th>
                            <th>Expenses</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td><strong>{{ $vendor->name }}</strong></td>
                                <td>{{ $vendor->contact_person ?? '—' }}</td>
                                <td>{{ $vendor->phone ?? '—' }}</td>
                                <td>{{ $vendor->expenses_count }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $vendor,
                                        'editRoute' => 'expenses.vendors.edit',
                                        'destroyRoute' => 'expenses.vendors.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $vendors->links() }}</div>
        @endif
    </div>
@endsection
