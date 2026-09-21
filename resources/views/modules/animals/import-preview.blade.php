@extends('layouts.dashboard')

@section('title', __('Import preview'))

@section('content')
    @php
        $newCount = (int) ($preview['new_count'] ?? 0);
        $existingCount = (int) ($preview['existing_count'] ?? 0);
        $failedCount = (int) ($preview['failed_count'] ?? 0);
        $total = (int) ($preview['total'] ?? 0);
        $hasExisting = $existingCount > 0;
        $canProceed = ($newCount + $existingCount) > 0;
    @endphp

    <div class="farm-dash farms-page health-page animal-import-page">
        @include('modules.partials.header', [
            'title' => __('Import preview'),
            'backRoute' => 'animals.import',
        ])
        @include('modules.partials.flash')

        <p class="animal-import-page__file-meta">
            {{ __('File: :name', ['name' => $pending['original_name'] ?? '—']) }}
            <span aria-hidden="true">·</span>
            {{ __('Nothing has been saved yet') }}
        </p>

        <section class="farm-dash__section" aria-label="{{ __('Preview summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Rows in file') }}</div>
                        <div class="farm-kpi__value">{{ number_format($total) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('New animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($newCount) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Existing animals') }}</div>
                        <div class="farm-kpi__value">{{ number_format($existingCount) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--expense">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Invalid rows') }}</div>
                        <div class="farm-kpi__value">{{ number_format($failedCount) }}</div>
                    </div>
                </div>
            </div>
        </section>

        @if ($hasExisting)
            <section class="farm-panel animal-import-page__table-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Existing animals found') }}</h2>
                        <p class="farm-panel__desc">
                            {{ __(':count animals in this file already exist (matched by tag within the group).', ['count' => $existingCount]) }}
                        </p>
                    </div>
                </header>
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Animal') }}</th>
                                <th>{{ __('Current name') }}</th>
                                <th>{{ __('Group') }}</th>
                                <th>{{ __('Row') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($preview['existing_rows'] ?? [], 0, 25) as $row)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'animal', 'tone' => 'warn'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $row['tag_number'] }}</span>
                                                <span class="health-table__meta">{{ $row['name'] ?: __('No name in file') }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $row['existing_name'] ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $row['livestock_name'] }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__meta">{{ $row['row'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (count($preview['existing_rows'] ?? []) > 25)
                    <p class="animal-import-page__note">{{ __('Showing the first 25 existing animals.') }}</p>
                @endif
            </section>
        @endif

        @if (! empty($preview['errors']))
            <section class="farm-panel animal-import-page__table-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Invalid rows') }}</h2>
                        <p class="farm-panel__desc">{{ __('These rows will not be imported. Valid rows can still proceed.') }}</p>
                    </div>
                </header>
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Row') }}</th>
                                <th>{{ __('Error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['errors'] as $error)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'certificate', 'tone' => 'bad'])
                                            <span class="health-table__value">{{ ($error['row'] ?? 0) > 0 ? $error['row'] : '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if (! empty($error['messages']) && count($error['messages']) > 1)
                                            <ul class="animal-import-page__error-list">
                                                @foreach ($error['messages'] as $message)
                                                    <li>{{ $message }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="health-table__value">{{ $error['message'] ?? '' }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="farm-panel">
            <header class="farm-panel__head">
                <div>
                    <h2 class="farm-panel__title">{{ __('Next step') }}</h2>
                    @if ($hasExisting)
                        <p class="farm-panel__desc">{{ __('Choose how to handle animals that already exist.') }}</p>
                    @elseif ($canProceed)
                        <p class="farm-panel__desc">{{ __('Ready to create new animals from this file.') }}</p>
                    @else
                        <p class="farm-panel__desc">{{ __('There are no valid animals to import from this file.') }}</p>
                    @endif
                </div>
            </header>

            <form method="POST" action="{{ route('animals.import.confirm') }}" class="animal-import-page__form">
                @csrf

                @if (! $canProceed)
                    <div class="animal-import-page__actions">
                        <button type="submit" name="duplicate_action" value="cancel" class="dash-farm-card__btn">{{ __('Cancel import') }}</button>
                        <a href="{{ route('animals.import') }}" class="dash-btn-save">{{ __('Upload a different file') }}</a>
                    </div>
                @elseif ($hasExisting)
                    <div class="animal-import-page__choice-grid">
                        <button type="submit" name="duplicate_action" value="replace" class="animal-import-page__choice">
                            <span class="animal-import-page__choice-title">{{ __('Replace existing') }}</span>
                            <span class="animal-import-page__choice-desc">{{ __('Update matching animals with values from this file.') }}</span>
                        </button>
                        <button type="submit" name="duplicate_action" value="keep" class="animal-import-page__choice">
                            <span class="animal-import-page__choice-title">{{ __('Keep existing') }}</span>
                            <span class="animal-import-page__choice-desc">{{ __('Leave current records unchanged and only import new animals.') }}</span>
                        </button>
                        <button type="submit" name="duplicate_action" value="cancel" class="animal-import-page__choice animal-import-page__choice--muted">
                            <span class="animal-import-page__choice-title">{{ __('Cancel import') }}</span>
                            <span class="animal-import-page__choice-desc">{{ __('Discard this upload. Nothing is saved.') }}</span>
                        </button>
                    </div>
                @else
                    <div class="animal-import-page__actions">
                        <button type="submit" name="duplicate_action" value="keep" class="dash-btn-save">
                            {{ __('Import :count new animals', ['count' => $newCount]) }}
                        </button>
                        <button type="submit" name="duplicate_action" value="cancel" class="dash-farm-card__btn">
                            {{ __('Cancel import') }}
                        </button>
                    </div>
                @endif

                @error('duplicate_action')
                    <p class="dash-form-error">{{ $message }}</p>
                @enderror
            </form>
        </section>
    </div>
@endsection
