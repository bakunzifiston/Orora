@extends('layouts.dashboard')

@section('content')
    @include('modules.expenses.partials.subnav', [
        'expenseSections' => $expenseSections ?? config('modules.expense_sections'),
        'activeExpenseSection' => $activeExpenseSection ?? 'overview',
    ])

    @yield('expense-content')
@endsection
