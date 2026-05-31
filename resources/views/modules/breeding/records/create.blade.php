@extends('layouts.breeding-module')

@section('title', 'Breeding — New record')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Record breeding',
        'subtitle' => 'Log a mating or artificial insemination event.',
        'backRoute' => 'breeding.records',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('breeding'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">{{ $errors->first('breeding') }}</div>
    @endif

    <form method="POST" action="{{ route('breeding.records.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.breeding.records._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save breeding record</button>
            </div>
        </div>
    </form>
@endsection
