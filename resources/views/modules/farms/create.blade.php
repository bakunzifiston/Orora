@extends('layouts.dashboard')

@section('title', __('Add farm'))

@section('content')
    @include('modules.partials.header', ['title' => __('Add farm'), 'backRoute' => 'farms.index'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('farms.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.farms._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Register farm') }}</button>
                <a href="{{ route('farms.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/farm-form.js'])
@endpush
