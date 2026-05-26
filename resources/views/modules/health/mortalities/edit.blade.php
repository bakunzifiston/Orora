@extends('layouts.health-module')

@section('title', 'Edit mortality record')

@section('health-content')
    @include('modules.partials.header', [
        'title' => 'Edit mortality record',
        'subtitle' => 'Update death details, reporting, and disposal information.',
        'backRoute' => 'health.mortality',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('health.mortalities.update', $mortality) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.health.mortalities._form', ['mortality' => $mortality])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save mortality record</button>
                <a href="{{ route('health.mortality') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
    @vite(['resources/js/select-other-form.js'])
@endsection
