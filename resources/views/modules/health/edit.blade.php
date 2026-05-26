@extends('layouts.health-module')

@section('title', 'Edit health record')

@section('health-content')
    @php
        $backRoute = collect(config('modules.health_sections'))->firstWhere('key', $returnSection ?? 'overview')['route'] ?? 'health.overview';
    @endphp
    @include('modules.partials.header', ['title' => 'Edit health record', 'backRoute' => $backRoute])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.records.update', $healthRecord) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="return_section" value="{{ $returnSection ?? 'overview' }}">
        @include('modules.health._form', ['healthRecord' => $healthRecord])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update record</button>
                <a href="{{ route($backRoute) }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/health-form.js'])
@endpush
