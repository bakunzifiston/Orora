@extends('layouts.health-module')

@section('title', __('Edit medicine'))

@section('health-content')
    @include('modules.partials.header', [
        'title' => __('Edit medicine'),
        'subtitle' => __('Update medicine details and stock levels.'),
        'backRoute' => 'health.medicines',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('health.medicines.update', $medicine) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.health.medicines._form', ['medicine' => $medicine])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Update medicine') }}</button>
                <a href="{{ route('health.medicines') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/select-other-form.js'])
@endpush
