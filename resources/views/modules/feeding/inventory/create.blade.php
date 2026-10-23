@extends('layouts.feeding-module')

@section('title', 'Add inventory')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Add inventory', 'backRoute' => 'feeding.inventory'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.inventory.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.feeding.inventory._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save inventory</button>
                <a href="{{ route('feeding.inventory') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
