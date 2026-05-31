@extends('layouts.expenses-module')

@section('title', 'Add category')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Add category', 'backRoute' => 'expenses.categories'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.categories.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.expenses.categories._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save category</button>
                <a href="{{ route('expenses.categories') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
