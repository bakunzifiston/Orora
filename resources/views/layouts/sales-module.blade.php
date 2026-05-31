@extends('layouts.dashboard')

@section('content')
    @include('modules.sales.partials.subnav', [
        'salesSections' => $salesSections ?? config('modules.sale_sections'),
        'activeSalesSection' => $activeSalesSection ?? 'overview',
    ])

    @yield('sales-content')
@endsection
