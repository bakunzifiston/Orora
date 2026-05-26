@extends('layouts.dashboard')

@section('content')
    @include('modules.feeding.partials.subnav', [
        'feedingSections' => $feedingSections ?? config('modules.feeding_sections'),
        'activeFeedingSection' => $activeFeedingSection ?? 'overview',
    ])

    @yield('feeding-content')
@endsection
