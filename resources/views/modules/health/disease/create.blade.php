@extends('layouts.health-module')

@section('title', 'New disease record')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'New disease record',
        'subtitle' => 'Log diagnosis, severity, recovery status, and clinical notes.',
        'backRoute' => 'health.disease',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.disease.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.health.disease._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save record</button>
                <a href="{{ route('health.disease') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection
