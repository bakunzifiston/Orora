@extends('layouts.customers-module')

@section('title', 'Customers — Edit')

@section('customer-content')
    @include('modules.partials.header', [
        'title' => 'Edit customer',
        'subtitle' => $customer->customer_code,
        'backRoute' => 'customers.show',
        'backRouteParams' => [$customer],
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('customers.update', $customer) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.customers._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Update customer</button>
            <a href="{{ route('customers.show', $customer) }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
