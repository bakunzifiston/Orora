@extends('layouts.feeding-module')

@section('title', 'Add supplier')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Add supplier', 'backRoute' => 'feeding.suppliers'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.suppliers.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.feeding.suppliers._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save supplier</button>
                <a href="{{ route('feeding.suppliers') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
