@extends('layouts.health-module')

@section('title', __('Add deworming'))

@section('health-content')
    @include('modules.partials.header', [
        'title' => __('Add deworming'),
        'subtitle' => __('Record dewormer product, schedule, and who was treated.'),
        'backRoute' => 'health.dewormings',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.dewormings.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.health.dewormings._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Save deworming') }}</button>
                <a href="{{ route('health.dewormings') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
