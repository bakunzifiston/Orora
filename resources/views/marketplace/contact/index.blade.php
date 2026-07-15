@extends('layouts.marketplace')

@section('title', 'Contact')

@section('meta_description', 'Contact Orora Farm for questions, support, or partnerships. We reply within 24 hours.')

@section('content')
    @include('marketplace.contact.partials.hero')
    @include('marketplace.contact.partials.options-strip')
    @include('marketplace.contact.partials.main-content')
    @include('marketplace.contact.partials.faq')
    @include('marketplace.contact.partials.map')
    @include('marketplace.contact.partials.cta')
@endsection

@push('body-scripts')
    @vite('resources/js/marketplace-contact.js')
@endpush
