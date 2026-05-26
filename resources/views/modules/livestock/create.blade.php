@extends('layouts.dashboard')

@section('title', 'Add livestock')

@section('content')
    @include('modules.partials.header', ['title' => 'Add livestock group', 'backRoute' => 'livestock.index'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('livestock.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.livestock._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save livestock</button>
                <a href="{{ route('livestock.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/livestock-form.js'])
@endpush
