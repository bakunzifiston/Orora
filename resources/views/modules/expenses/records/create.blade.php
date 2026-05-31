@extends('layouts.expenses-module')

@section('title', 'Add expense')

@section('expense-content')
    @include('modules.partials.header', ['title' => 'Add expense', 'backRoute' => 'expenses.records'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('expenses.records.store') }}" class="dash-profile-form" enctype="multipart/form-data">
            @csrf
            @include('modules.expenses.records._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save expense</button>
                <a href="{{ route('expenses.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
