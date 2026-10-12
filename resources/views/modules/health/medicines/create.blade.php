@extends('layouts.health-module')

@section('title', __('Add medicine'))

@section('health-content')
    @include('modules.partials.header', [
        'title' => __('Add medicine'),
        'subtitle' => __('Register medicine stock used during animal treatments.'),
        'backRoute' => 'health.medicines',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('health.medicines.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.health.medicines._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Save medicine') }}</button>
                <a href="{{ route('health.medicines') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/select-other-form.js'])
@endpush
