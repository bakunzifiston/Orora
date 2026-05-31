@extends('layouts.milk-module')

@section('title', 'Record milk')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Record milk',
        'subtitle' => 'Log milk collected from an animal.',
        'backRoute' => 'milk.records',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.records.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.milk.records._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save milk record</button>
                <a href="{{ route('milk.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection
