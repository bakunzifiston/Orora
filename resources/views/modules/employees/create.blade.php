@extends('layouts.employees-module')

@section('title', 'Employees — Add')

@section('employee-content')
    @include('modules.partials.header', ['title' => 'Register employee', 'backRoute' => 'employees.directory'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('employees.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.employees._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Save employee</button>
            <a href="{{ route('employees.directory') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
