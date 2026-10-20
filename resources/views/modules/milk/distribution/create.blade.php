@extends('layouts.milk-module')

@section('title', __('Record milk use'))

@section('milk-content')
    @include('modules.partials.header', ['title' => __('Record milk use'), 'backRoute' => 'milk.distribution'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.distribution.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.milk.distribution._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">{{ __('Save') }}</button>
            <a href="{{ route('milk.distribution') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/milk-distribution-form.js'])
@endpush
