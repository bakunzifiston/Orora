@extends('layouts.milk-module')

@section('title', 'Add milk storage')

@section('milk-content')
    @include('modules.partials.header', ['title' => 'Add storage container', 'backRoute' => 'milk.storage'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.storage.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.milk.storage._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Save</button>
            <a href="{{ route('milk.storage') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
