@extends('layouts.employees-module')

@section('title', 'Employees — Edit '.$employee->display_name)

@section('employee-content')
    @include('modules.partials.header', [
        'title' => 'Edit employee',
        'subtitle' => $employee->employee_code,
        'backRoute' => 'employees.show',
        'backRouteParams' => [$employee],
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('employees.update', $employee) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.employees._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Update employee</button>
            <a href="{{ route('employees.show', $employee) }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
