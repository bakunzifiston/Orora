@extends('layouts.expenses-module')

@section('title', 'Add vendor')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Add vendor', 'backRoute' => 'expenses.vendors'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.vendors.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.expenses.vendors._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save vendor</button>
                <a href="{{ route('expenses.vendors') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
