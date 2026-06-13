@extends('layouts.marketplace')

@section('title', 'About')

@section('meta_description', 'Learn about Orora Farm — modern farm management built in Rwanda for farmers across Africa.')

@section('content')
    @include('marketplace.about.partials.hero')
    @include('marketplace.about.partials.our-story')
    @include('marketplace.about.partials.our-mission')
    @include('marketplace.about.partials.what-we-offer')
    @include('marketplace.about.partials.our-values')
    @include('marketplace.about.partials.stats')
    @include('marketplace.about.partials.our-team')
    @include('marketplace.about.partials.why-rwanda')
    @include('marketplace.about.partials.testimonials')
    @include('marketplace.about.partials.partners')
    @include('marketplace.about.partials.cta')
@endsection

@push('body-scripts')
    @vite('resources/js/marketplace-about.js')
@endpush
