@extends('layouts.dashboard')

@section('title', 'Add certificate')

@section('content')
    @include('modules.partials.header', ['title' => 'Add certificate', 'backRoute' => 'certificates.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('certificates.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.certificates._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save</button>
                <a href="{{ route('certificates.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
