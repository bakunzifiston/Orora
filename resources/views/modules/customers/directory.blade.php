@extends('layouts.customers-module')

@section('title', __('Customers — Directory'))

@section('customer-content')
    @include('modules.partials.header', [
        'title' => __('Customer directory'),
        'subtitle' => __('Tenant-wide — customers can buy from any farm.'),
        'createRoute' => 'customers.create',
        'createLabel' => '+ '. __('Add customer'),
        'secondaryLinks' => [
            [
                'route' => 'customers.export',
                'params' => request()->query(),
                'label' => __('Export CSV'),
            ],
            [
                'route' => 'customers.import',
                'label' => __('Import'),
            ],
        ],
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('customers.directory') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_q">{{ __('Search') }}</label>
                <input type="search" name="q" id="filter_q" value="{{ $filterQuery }}" placeholder="{{ __('Name or code') }}">
            </div>
            <div class="dash-form-field">
                <label for="filter_type">{{ __('Type') }}</label>
                <select name="type" id="filter_type">
                    <option value="">{{ __('All types') }}</option>
                    @foreach (config('modules.customer_types') as $value => $label)
                        <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_status">{{ __('Status') }}</label>
                <select name="status" id="filter_status">
                    <option value="">{{ __('All statuses') }}</option>
                    @foreach (config('modules.customer_statuses') as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">{{ __('Filter') }}</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($customers->isEmpty())
            <p class="dash-empty">{{ __('No customers match your filters.') }}</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Outstanding') }}</th>
                            <th>{{ __('Sales') }}</th>
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
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $customer,
                                        'editRoute' => 'customers.edit',
                                        'destroyRoute' => 'customers.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
