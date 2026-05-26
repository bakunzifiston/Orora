@extends('layouts.feeding-module')

@section('title', 'Edit feeding')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Edit feeding record',
        'backRoute' => 'feeding.records',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('feeding.records.update', $feeding) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.feedings._form', ['feeding' => $feeding])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update</button>
                <a href="{{ route('feeding.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
