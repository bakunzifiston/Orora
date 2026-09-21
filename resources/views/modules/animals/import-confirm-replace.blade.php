@extends('layouts.dashboard')

@section('title', __('Confirm replace'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Confirm replace'),
        'subtitle' => __('Existing animal records will be updated with values from your file.'),
        'backRoute' => 'animals.import.preview',
    ])
    @include('modules.partials.flash')

    <div class="dash-farm-form">
        @component('modules.farms._form-section', [
            'number' => '!',
            'title' => __('Final confirmation'),
            'description' => __('This cannot be undone from the import screen.'),
        ])
            <p style="font-size: 1.05rem; margin-bottom: 0.75rem;">
                {{ __(':count existing animals will be updated.', ['count' => $existingCount]) }}
            </p>
            <p class="dash-form-hint" style="margin-bottom: 1rem;">
                {{ __('Their current information will be replaced with the values from this file. New animals will still be created. Invalid rows will be skipped.') }}
            </p>

            <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('New animals') }}</div>
                    <div class="dash-stat-value accent">{{ number_format($preview['new_count'] ?? 0) }}</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('Will be updated') }}</div>
                    <div class="dash-stat-value">{{ number_format($existingCount) }}</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('Invalid rows') }}</div>
                    <div class="dash-stat-value">{{ number_format($preview['failed_count'] ?? 0) }}</div>
                </div>
            </div>
        @endcomponent

        <div class="dash-form-section dash-form-section--actions">
            <div class="dash-form-section__body">
                <div class="dash-form-actions" style="flex-wrap: wrap; gap: 0.75rem;">
                    <form method="POST" action="{{ route('animals.import.execute-replace') }}">
                        @csrf
                        <button type="submit" class="dash-btn-save">{{ __('Confirm replace') }}</button>
                    </form>
                    <form method="POST" action="{{ route('animals.import.cancel') }}">
                        @csrf
                        <button type="submit" class="dash-btn-cancel">{{ __('Cancel') }}</button>
                    </form>
                    <a href="{{ route('animals.import.preview') }}" class="dash-btn-cancel">{{ __('Back to preview') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
