@extends('layouts.dashboard')

@section('content')
    @include('modules.breeding.partials.subnav', [
        'breedingSections' => $breedingSections ?? config('modules.breeding_sections'),
        'activeBreedingSection' => $activeBreedingSection ?? 'overview',
    ])

    @yield('breeding-content')
@endsection
