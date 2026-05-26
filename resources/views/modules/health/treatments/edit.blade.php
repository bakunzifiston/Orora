@extends('layouts.health-module')

@section('title', 'Edit treatment')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Edit treatment',
        'subtitle' => 'Update disease, medication, schedule, and clinical notes.',
        'backRoute' => 'health.treatments',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.treatments.update', $treatment) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.health.treatments._form', ['treatment' => $treatment])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save treatment</button>
                <a href="{{ route('health.treatments') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
