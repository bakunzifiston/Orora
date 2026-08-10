@extends('layouts.dashboard')

@section('title', __('Edit farm'))

@section('content')
    @include('modules.partials.header', ['title' => __('Edit farm'), 'backRoute' => 'farms.show', 'backRouteParams' => [$farm]])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('farms.update', $farm) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.farms._form', ['farm' => $farm])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">{{ __('Update farm') }}</button>
                <a href="{{ route('farms.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/farm-form.js'])
@endpush
