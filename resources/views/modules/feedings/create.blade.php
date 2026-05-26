@extends('layouts.feeding-module')

@section('title', 'Log feeding')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Log feeding',
        'backRoute' => 'feeding.records',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.records.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.feedings._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save</button>
                <a href="{{ route('feeding.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
