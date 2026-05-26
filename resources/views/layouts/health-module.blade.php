@extends('layouts.dashboard')

@section('content')
    @include('modules.health.partials.subnav', [
        'healthSections' => $healthSections ?? config('modules.health_sections'),
        'activeHealthSection' => $activeHealthSection ?? 'overview',
    ])

    @yield('health-content')
@endsection
