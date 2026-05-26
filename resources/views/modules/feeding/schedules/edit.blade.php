@extends('layouts.feeding-module')

@section('title', 'Edit schedule')

@section('feeding-content')
    @include('modules.partials.header', ['title' => 'Edit feeding schedule', 'backRoute' => 'feeding.schedules'])
    @include('modules.partials.flash')
    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.schedules.update', $schedule) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.feeding.schedules._form', ['schedule' => $schedule])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save schedule</button>
                <a href="{{ route('feeding.schedules') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
