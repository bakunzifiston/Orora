@extends('layouts.dashboard')

@section('title', 'Edit movement')

@section('content')
    @include('modules.partials.header', ['title' => 'Edit movement', 'backRoute' => 'movements.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('movements.update', $movement) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.movements._form', ['movement' => $movement])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update</button>
                <a href="{{ route('movements.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
