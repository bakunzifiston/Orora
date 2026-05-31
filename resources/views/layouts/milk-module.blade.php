@extends('layouts.dashboard')

@section('content')
    @include('modules.milk.partials.subnav', [
        'milkSections' => $milkSections ?? config('modules.milk_sections'),
        'activeMilkSection' => $activeMilkSection ?? 'overview',
    ])

    @yield('milk-content')
@endsection
