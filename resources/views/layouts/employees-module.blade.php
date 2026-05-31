@extends('layouts.dashboard')

@section('content')
    @include('modules.employees.partials.subnav', [
        'employeeSections' => $employeeSections ?? config('modules.employee_sections'),
        'activeEmployeeSection' => $activeEmployeeSection ?? 'overview',
    ])

    @yield('employee-content')
@endsection
