@extends('layouts.feeding-module')

@section('title', 'Edit supplier')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Edit supplier', 'backRoute' => 'feeding.suppliers'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.suppliers.update', $supplier) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.feeding.suppliers._form', ['supplier' => $supplier])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save supplier</button>
                <a href="{{ route('feeding.suppliers') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
