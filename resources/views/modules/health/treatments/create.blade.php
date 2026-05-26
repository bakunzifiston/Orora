@extends('layouts.health-module')

@section('title', 'Add treatment')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Add treatment',
        'subtitle' => 'Record disease, medication, schedule, and clinical notes.',
        'backRoute' => 'health.treatments',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.treatments.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.health.treatments._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save treatment</button>
                <a href="{{ route('health.treatments') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
