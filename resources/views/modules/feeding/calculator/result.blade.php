@extends('layouts.feeding-module')

@section('title', 'Feed calculator results')

@section('feeding-content')
    @include('modules.partials.header', [
        'title' => 'Feed requirements',
        'subtitle' => $result['has_data']
            ? ($result['level'] === 'herd'
                ? collect([$result['label'], $result['farm_name']])->filter()->implode(' — ')
                : $result['label'])
            : 'Could not calculate',
        'backRoute' => 'feeding.calculator.index',
        'backLabel' => 'Back to calculator',
    ])
    @include('modules.partials.flash')

    @if (! $result['has_data'])
        <div class="dash-panel">
            <p class="dash-empty">{{ $result['reason'] ?? 'No recommendation available.' }}</p>
            <div class="dash-form-actions" style="margin-top: 1rem;">
                <a href="{{ route('feeding.calculator.index') }}" class="dash-btn-save">Try again</a>
            </div>
        </div>
    @elseif ($result['level'] === 'individual')
        @include('modules.feeding.calculator._result-individual')
    @else
        @include('modules.feeding.calculator._result-herd')
    @endif

    @if ($result['has_data'])
        <div class="dash-form-actions" style="margin-top: 1rem;">
            <a href="{{ route('feeding.calculator.index') }}" class="dash-btn-save">Recalculate</a>
            <a href="{{ route('feeding.calculator.index') }}" class="dash-btn-cancel">Back to calculator</a>
        </div>
    @endif
@endsection
