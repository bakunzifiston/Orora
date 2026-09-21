@extends('layouts.dashboard')

@section('title', __('Confirm replace'))

@section('content')
    <div class="farm-dash farms-page health-page animal-import-page">
        @include('modules.partials.header', [
            'title' => __('Confirm replace'),
            'backRoute' => 'animals.import.preview',
        ])
        @include('modules.partials.flash')

        <section class="farm-dash__section" aria-label="{{ __('Replace summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--3">
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('New animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($preview['new_count'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Will be updated') }}</div>
                        <div class="farm-kpi__value">{{ number_format($existingCount) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Invalid rows') }}</div>
                        <div class="farm-kpi__value">{{ number_format($preview['failed_count'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Final confirmation') }}</h2>
                    <p class="farm-panel__desc">
                        {{ __(':count existing animals will be updated with values from your file. New animals will still be created. Invalid rows will be skipped.', ['count' => $existingCount]) }}
                    </p>
                </div>
            </header>
            <div class="animal-import-page__actions">
                <form method="POST" action="{{ route('animals.import.execute-replace') }}">
                    @csrf
                    <button type="submit" class="dash-btn-save">{{ __('Confirm replace') }}</button>
                </form>
                <a href="{{ route('animals.import.preview') }}" class="dash-farm-card__btn">{{ __('Back to preview') }}</a>
                <form method="POST" action="{{ route('animals.import.cancel') }}">
                    @csrf
                    <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Cancel') }}</button>
                </form>
            </div>
        </section>
    </div>
@endsection
