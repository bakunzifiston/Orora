@extends('layouts.dashboard')

@section('content')
    @include('modules.finance.partials.subnav', [
        'financeSections' => $financeSections ?? config('finance.finance_sections'),
        'activeFinanceSection' => $activeFinanceSection ?? 'overview',
    ])

    @yield('finance-content')
@endsection
