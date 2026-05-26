@extends('layouts.health-module')

@section('title', 'Edit vaccination')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Edit vaccination',
        'subtitle' => 'Update vaccine details, schedule, and observations.',
        'backRoute' => 'health.vaccinations',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.vaccinations.update', $vaccination) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.health.vaccinations._form', ['vaccination' => $vaccination])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save vaccination</button>
                <a href="{{ route('health.vaccinations') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
