@extends('layouts.milk-module')

@section('title', __('Edit milk use'))

@section('milk-content')
    @include('modules.partials.header', [
        'title' => __('Edit milk use'),
        'subtitle' => $milkDistribution->distribution_code.' · '.__('recorded by :name', ['name' => $milkDistribution->recorder?->name ?? '—']),
        'backRoute' => 'milk.distribution',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.distribution.update', $milkDistribution) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.milk.distribution._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">{{ __('Save changes') }}</button>
            <a href="{{ route('milk.distribution') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/milk-distribution-form.js'])
@endpush
