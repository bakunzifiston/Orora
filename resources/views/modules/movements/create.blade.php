@extends('layouts.dashboard')

@section('title', 'Record movement')

@section('content')
    @include('modules.partials.header', ['title' => 'Record movement', 'backRoute' => 'movements.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('movements.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.movements._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save</button>
                <a href="{{ route('movements.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
