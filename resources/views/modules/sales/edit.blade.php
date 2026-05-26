@extends('layouts.dashboard')

@section('title', 'Edit sale')

@section('content')
    @include('modules.partials.header', ['title' => 'Edit sale', 'backRoute' => 'sales.index'])
    @include('modules.partials.flash')

    <div class="dash-panel dash-profile-panel">
        <form method="POST" action="{{ route('sales.update', $sale) }}" class="dash-profile-form">
            @csrf
            @method('PUT')
            @include('modules.sales._form', ['sale' => $sale])
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Update</button>
                <a href="{{ route('sales.index') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
@endsection
