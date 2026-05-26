@extends('layouts.dashboard')

@section('title', 'Register animal')

@section('content')
    @include('modules.partials.header', ['title' => 'Register animal', 'backRoute' => 'animals.index'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('animals.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.animals._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Register animal</button>
                <a href="{{ route('animals.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/animal-form.js'])
@endpush
