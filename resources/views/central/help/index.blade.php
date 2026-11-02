@extends('layouts.admin')

@section('title', __('Help center'))

@section('content')
    @include('help.partials.documents', ['downloadRoute' => 'central.help.download', 'heading' => __('Help center'), 'intro' => __('OroraFarm manuals for staff, administrators and developers. Technical documentation is visible to platform administrators only.')])
@endsection
