@extends('layouts.expenses-module')

@section('title', 'Edit expense')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Edit expense', 'backRoute' => 'expenses.records'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.records.update', $expense) }}" class="dash-profile-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('modules.expenses.records._form', ['expense' => $expense])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save expense</button>
                <a href="{{ route('expenses.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
