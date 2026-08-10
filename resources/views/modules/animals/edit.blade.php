@extends('layouts.dashboard')

@section('title', 'Edit animal')

@section('content')
    @include('modules.partials.header', ['title' => __('Edit animal'), 'backRoute' => 'animals.show', 'backRouteParams' => [$animal]])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('animals.update', $animal) }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('modules.animals._form', ['animal' => $animal])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update animal</button>
                <a href="{{ route('animals.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    @vite(['resources/js/animal-form.js'])
@endpush
