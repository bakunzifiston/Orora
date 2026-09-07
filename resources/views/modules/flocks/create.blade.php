@extends('layouts.dashboard')

@section('title', 'Place flock')

@section('content')
    @include('modules.partials.header', ['title' => __('Place flock'), 'backRoute' => 'flocks.index'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('flocks.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.flocks._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-section__body">
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">{{ __('Place flock') }}</button>
                    <a href="{{ route('flocks.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection
