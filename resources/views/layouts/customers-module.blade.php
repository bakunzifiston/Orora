@extends('layouts.dashboard')

@section('content')
    @include('modules.customers.partials.subnav', [
        'customerSections' => $customerSections ?? config('modules.customer_sections'),
        'activeCustomerSection' => $activeCustomerSection ?? 'overview',
    ])

    @yield('customer-content')
@endsection
