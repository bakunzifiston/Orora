@extends('layouts.dashboard')

@section('title', 'Record sale')

@section('content')
    @include('modules.partials.header', ['title' => 'Record sale', 'backRoute' => 'sales.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('sales.store') }}" class="dash-profile-form">
            @csrf
            @include('modules.sales._form')
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save</button>
                <a href="{{ route('sales.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
