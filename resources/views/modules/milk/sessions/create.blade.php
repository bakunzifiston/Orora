@extends('layouts.milk-module')

@section('title', 'Open milking session')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Open milking session',
        'subtitle' => 'Start a herd milking event, then add animal yields.',
        'backRoute' => 'milk.sessions',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.sessions.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.milk.sessions._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Open session</button>
                <a href="{{ route('milk.sessions') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection
