@extends('layouts.milk-module')

@section('title', 'Edit milk record')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Edit milk record',
        'subtitle' => 'Update production and quality details.',
        'backRoute' => 'milk.records',
    ])
    @include('modules.partials.flash')

    <form method="POST" action="{{ route('milk.records.update', $milkRecord) }}" class="dash-farm-form">
        @csrf
        @method('PUT')
        @include('modules.milk.records._form', ['milkRecord' => $milkRecord])
        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-actions">
                <button type="submit" class="dash-btn-save">Save milk record</button>
                <a href="{{ route('milk.records') }}" class="dash-btn-cancel">Cancel</a>
            </div>
        </div>
    </form>
@endsection
