@extends('layouts.customers-module')

@section('title', 'Customers — Directory')

@section('customer-content')
    @include('modules.partials.header', [
        'title' => 'Customer directory',
        'subtitle' => 'Tenant-wide — customers can buy from any farm.',
        'createRoute' => 'customers.create',
        'createLabel' => '+ Add customer',
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('customers.directory') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_q">Search</label>
                <input type="search" name="q" id="filter_q" value="{{ $filterQuery }}" placeholder="Name or code">
            </div>
            <div class="dash-form-field">
                <label for="filter_type">Type</label>
                <select name="type" id="filter_type">
                    <option value="">All types</option>
                    @foreach (config('modules.customer_types') as $value => $label)
                        <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_status">Status</label>
                <select name="status" id="filter_status">
                    <option value="">All statuses</option>
                    @foreach (config('modules.customer_statuses') as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">Filter</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($customers->isEmpty())
            <p class="dash-empty">No customers match your filters.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Outstanding</th>
                            <th>Sales</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>{{ $customer->customer_code }}</td>
                                <td><a href="{{ route('customers.show', $customer) }}"><strong>{{ $customer->display_name }}</strong></a></td>
                                <td>{{ $customer->typeLabel() }}</td>
                                <td>{{ ucfirst($customer->status) }}</td>
                                <td>{{ number_format($customer->credit?->outstanding_balance ?? 0, 0) }} {{ $customer->currency }}</td>
                                <td>{{ $customer->sale_transactions_count }}</td>
                                <td><a href="{{ route('customers.edit', $customer) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
