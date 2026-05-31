@extends('layouts.expenses-module')

@section('title', 'Edit category')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Edit category', 'backRoute' => 'expenses.categories'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.categories.update', $category) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.expenses.categories._form', ['category' => $category])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save category</button>
                <a href="{{ route('expenses.categories') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
