@extends('layouts.feeding-module')

@section('title', 'Edit feed type')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Edit feed type', 'backRoute' => 'feeding.feed-types'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.feed-types.update', $feedType) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.feeding.feed-types._form', ['feedType' => $feedType])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save feed type</button>
                <a href="{{ route('feeding.feed-types') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
