@extends('layouts.dashboard')

@section('title', __('Help center'))

@section('content')
    @include('help.partials.documents', ['downloadRoute' => 'help.download', 'heading' => __('Help center'), 'intro' => __('Download the OroraFarm user guides as PDF.')])
@endsection
