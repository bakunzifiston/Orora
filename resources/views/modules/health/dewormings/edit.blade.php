@extends('layouts.health-module')

@section('title', __('Edit deworming'))

@section('health-content')
    @include('modules.partials.header', [
        'title' => __('Edit deworming'),
        'subtitle' => __('Update dewormer product, schedule, and observations.'),
        'backRoute' => 'health.dewormings',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.dewormings.update', $deworming) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.health.dewormings._form', ['deworming' => $deworming])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Save deworming') }}</button>
                <a href="{{ route('health.dewormings') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
