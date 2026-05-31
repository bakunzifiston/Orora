@extends('layouts.customers-module')

@section('title', 'Customers — Add')

@section('customer-content')
    @include('modules.partials.header', ['title' => 'Register customer', 'backRoute' => 'customers.directory'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('customers.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.customers._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Save customer</button>
            <a href="{{ route('customers.directory') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
