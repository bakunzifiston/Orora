@extends('layouts.eggs-module')

@section('title', 'Record egg collection')

@section('eggs-content')
    @include('modules.partials.header', ['title' => __('Record collection'), 'backRoute' => 'eggs.collections'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('eggs.collections.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.eggs.collections._form')
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-section__body">
                <div class="dash-form-actions">
                    <button type="submit" class="dash-btn-save">{{ __('Save collection') }}</button>
                    <a href="{{ route('eggs.collections') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                </div>
            </div>
        </div>
    </form>
@endsection
