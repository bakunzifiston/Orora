@extends('layouts.marketplace')

@section('title', 'Post a Listing')

@section('content')
    <section class="shop-form-page">
        <div class="mp-container shop-form-page__inner">
            <header class="shop-form-page__header">
                <h1>Post a New Listing</h1>
                <p>Reach buyers across Rwanda</p>
            </header>

            @if ($errors->any())
                <div class="mp-alert" role="alert">
                    Please fix the errors below and try again.
                </div>
            @endif

            @include('marketplace.shop.partials._form')
        </div>
    </section>
@endsection
