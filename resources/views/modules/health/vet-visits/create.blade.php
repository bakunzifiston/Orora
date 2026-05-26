@extends('layouts.health-module')

@section('title', 'Add vet visit')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Add vet visit',
        'subtitle' => 'Record visit details, medication, schedule, and clinical notes.',
        'backRoute' => 'health.vet-visits',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.vet-visits.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.health.vet-visits._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save vet visit</button>
                <a href="{{ route('health.vet-visits') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
