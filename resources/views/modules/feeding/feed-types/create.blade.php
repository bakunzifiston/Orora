@extends('layouts.feeding-module')

@section('title', 'Add feed type')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Add feed type', 'backRoute' => 'feeding.feed-types'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.feed-types.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.feeding.feed-types._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save feed type</button>
                <a href="{{ route('feeding.feed-types') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
