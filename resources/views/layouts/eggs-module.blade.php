@extends('layouts.dashboard')

@section('content')
    @include('modules.eggs.partials.subnav', [
        'eggSections' => $eggSections ?? config('modules.egg_sections'),
        'activeEggSection' => $activeEggSection ?? 'overview',
    ])

    @yield('eggs-content')
@endsection
