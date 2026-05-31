@extends('layouts.breeding-module')

@section('title', 'Breeding — New pregnancy check')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Pregnancy check',
        'subtitle' => 'Confirm pregnancy after breeding.',
        'backRoute' => 'breeding.checks',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('check'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">{{ $errors->first('check') }}</div>
    @endif

    <form method="POST" action="{{ route('breeding.checks.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.breeding.checks._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save check</button>
            </div>
        </div>
    </form>
@endsection
