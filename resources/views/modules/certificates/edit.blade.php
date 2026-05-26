@extends('layouts.dashboard')

@section('title', 'Edit certificate')

@section('content')
    @include('modules.partials.header', ['title' => 'Edit certificate', 'backRoute' => 'certificates.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('certificates.update', $certificate) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.certificates._form', ['certificate' => $certificate])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update</button>
                <a href="{{ route('certificates.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
