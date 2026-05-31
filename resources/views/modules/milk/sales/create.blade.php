@extends('layouts.milk-module')

@section('title', 'New milk sale')

@section('milk-content')
    @include('modules.partials.header', ['title' => 'New milk sale', 'backRoute' => 'milk.sales'])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.sales.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.milk.sales._form')
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Create draft sale</button>
            <a href="{{ route('milk.sales') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
