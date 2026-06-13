@extends('layouts.health-module')

@section('title', 'Edit disease record')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Edit disease record',
        'subtitle' => $diseaseRecord->disease_code.' · '.$diseaseRecord->disease_name,
        'backRoute' => 'health.disease',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.disease.update', $diseaseRecord) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.health.disease._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save changes</button>
                <a href="{{ route('health.disease') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection
