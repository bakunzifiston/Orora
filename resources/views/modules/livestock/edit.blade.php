@extends('layouts.dashboard')

@section('title', 'Edit livestock')

@section('content')
    @include('modules.partials.header', ['title' => 'Edit livestock group', 'backRoute' => 'livestock.index'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('livestock.update', $livestock) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.livestock._form', ['livestock' => $livestock])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update livestock</button>
                <a href="{{ route('livestock.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/livestock-form.js'])
@endpush
