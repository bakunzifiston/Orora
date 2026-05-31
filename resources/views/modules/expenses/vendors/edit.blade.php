@extends('layouts.expenses-module')

@section('title', 'Edit vendor')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Edit vendor', 'backRoute' => 'expenses.vendors'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.vendors.update', $vendor) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.expenses.vendors._form', ['vendor' => $vendor])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save vendor</button>
                <a href="{{ route('expenses.vendors') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
