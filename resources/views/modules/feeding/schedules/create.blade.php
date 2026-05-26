@extends('layouts.feeding-module')

@section('title', 'Add schedule')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Add feeding schedule', 'backRoute' => 'feeding.schedules'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.schedules.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.feeding.schedules._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save schedule</button>
                <a href="{{ route('feeding.schedules') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
