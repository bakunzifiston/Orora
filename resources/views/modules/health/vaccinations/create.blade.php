@extends('layouts.health-module')

@section('title', 'Add vaccination')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Add vaccination',
        'subtitle' => 'Record vaccine details, schedule, and provider information.',
        'backRoute' => 'health.vaccinations',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.vaccinations.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.health.vaccinations._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save vaccination</button>
                <a href="{{ route('health.vaccinations') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
