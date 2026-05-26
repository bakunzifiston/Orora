@extends('layouts.health-module')

@section('title', 'Health — '.$title)

@section('health-content')
    @php
        $typeMap = [
            'vaccinations' => 'Vaccination',
            'treatments' => 'Treatment',
            'vet-visits' => 'Vet visit',
            'mortality' => 'Mortality',
        ];
        $createType = $typeMap[$section] ?? null;
    @endphp
    @include('modules.partials.header', [
        'title' => $title,
        'subtitle' => $subtitle,
        'createRoute' => 'health.records.create',
        'createRouteParams' => array_filter(['section' => $section, 'type' => $createType]),
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @include('modules.health.partials.records-table', [
            'healthRecords' => $healthRecords,
            'section' => $section,
            'createType' => $createType,
            'emptyMessage' => 'No records in '.$title.' yet.',
        ])
    </div>
@endsection
