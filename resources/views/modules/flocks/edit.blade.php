@extends('layouts.dashboard')

@section('title', 'Edit flock')

@section('content')
    @include('modules.partials.header', [
        'title' => __('Edit flock'),
        'backRoute' => 'flocks.show',
        'backRouteParams' => [$flock],
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('flocks.update', $flock) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.flocks._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-section__body">
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">{{ __('Save flock') }}</button>
                    <a href="{{ route('flocks.show', $flock) }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection
