@extends('layouts.breeding-module')

@section('title', 'Breeding — Record birth')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Record birth',
        'subtitle' => 'Log calving or kidding and create offspring rows.',
        'backRoute' => 'breeding.births',
    ])
    @include('modules.partials.flash')

    @if ($errors->has('birth'))
        <div class="dash-flash dash-flash--error" style="margin-bottom: 1rem;">{{ $errors->first('birth') }}</div>
    @endif

    <form method="POST" action="{{ route('breeding.births.store') }}" class="dash-farm-form" enctype="multipart/form-data">
        @csrf
        @include('modules.breeding.births._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save birth record</button>
            </div>
        </div>
    </form>
@endsection
