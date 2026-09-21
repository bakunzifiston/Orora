@extends('layouts.dashboard')

@section('title', __('Import preview'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Import preview'),
        'subtitle' => __('Review the file before any animals are created or updated.'),
        'backRoute' => 'animals.import',
    ])
    @include('modules.partials.flash')

    @php
        $newCount = (int) ($preview['new_count'] ?? 0);
        $existingCount = (int) ($preview['existing_count'] ?? 0);
        $failedCount = (int) ($preview['failed_count'] ?? 0);
        $total = (int) ($preview['total'] ?? 0);
        $hasExisting = $existingCount > 0;
        $canProceed = ($newCount + $existingCount) > 0;
    @endphp

    <div class="dash-farm-form">
        @component('modules.farms._form-section', [
            'number' => '1',
            'title' => __('Import preview'),
            'description' => __('No database changes have been made yet. Existing animals are matched by tag number within the livestock group.'),
        ])
            <p class="dash-form-hint" style="margin-bottom: 1rem;">
                {{ __('File: :name', ['name' => $pending['original_name'] ?? '—']) }}
            </p>
            <div class="dash-health-stats">
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('Rows in file') }}</div>
                    <div class="dash-stat-value">{{ number_format($total) }}</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('New animals') }}</div>
                    <div class="dash-stat-value accent">{{ number_format($newCount) }}</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('Existing animals') }}</div>
                    <div class="dash-stat-value">{{ number_format($existingCount) }}</div>
                </div>
                <div class="dash-stat-card">
                    <div class="dash-stat-label">{{ __('Invalid rows') }}</div>
                    <div class="dash-stat-value">{{ number_format($failedCount) }}</div>
                </div>
            </div>
        @endcomponent

        @if ($hasExisting)
            @component('modules.farms._form-section', [
                'number' => '2',
                'title' => __('Existing animals found'),
                'description' => __(':count animals in this file already exist in the system.', ['count' => $existingCount]),
            ])
                <div class="dash-table-wrap" style="margin-bottom: 1.25rem;">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th class="dash-table__row-col">{{ __('Row') }}</th>
                                <th>{{ __('Tag') }}</th>
                                <th>{{ __('File name') }}</th>
                                <th>{{ __('Current name') }}</th>
                                <th>{{ __('Group') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (array_slice($preview['existing_rows'] ?? [], 0, 25) as $row)
                                <tr>
                                    <td>{{ $row['row'] }}</td>
                                    <td>{{ $row['tag_number'] }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['existing_name'] }}</td>
                                    <td>{{ $row['livestock_name'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (count($preview['existing_rows'] ?? []) > 25)
                    <p class="dash-form-hint">{{ __('Showing the first 25 existing animals.') }}</p>
                @endif

                <p style="margin-bottom: 0.75rem; font-weight: 600;">{{ __('What would you like to do?') }}</p>
            @endcomponent
        @endif

        @if (! empty($preview['errors']))
            @component('modules.farms._form-section', [
                'number' => $hasExisting ? '3' : '2',
                'title' => __('Invalid rows'),
                'description' => __('These rows will not be imported. Valid rows can still proceed.'),
            ])
                <div class="dash-table-wrap">
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th class="dash-table__row-col">{{ __('Row') }}</th>
                                <th>{{ __('Error') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($preview['errors'] as $error)
                                <tr>
                                    <td>{{ ($error['row'] ?? 0) > 0 ? $error['row'] : '—' }}</td>
                                    <td>
                                        @if (! empty($error['messages']) && count($error['messages']) > 1)
                                            <ul style="margin: 0; padding-left: 1.1rem;">
                                                @foreach ($error['messages'] as $message)
                                                    <li>{{ $message }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            {{ $error['message'] ?? '' }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endcomponent
        @endif

        <form method="POST" action="{{ route('animals.import.confirm') }}" class="animal-import-confirm-form">
            @csrf

            <div class="dash-form-section dash-form-section--actions">
                <div class="dash-form-section__body">
                    @if (! $canProceed)
                        <p class="dash-form-hint" style="margin-bottom: 1rem;">
                            {{ __('There are no valid animals to import from this file.') }}
                        </p>
                        <div class="dash-form-actions">
                            <button type="submit" name="duplicate_action" value="cancel" class="dash-btn-cancel">{{ __('Cancel import') }}</button>
                            <a href="{{ route('animals.import') }}" class="dash-btn-cancel">{{ __('Upload a different file') }}</a>
                        </div>
                    @elseif ($hasExisting)
                        <div class="dash-form-actions" style="flex-wrap: wrap; gap: 0.75rem;">
                            <button type="submit" name="duplicate_action" value="replace" class="dash-btn-save">
                                {{ __('Replace existing') }}
                            </button>
                            <button type="submit" name="duplicate_action" value="keep" class="dash-btn-save" style="background: transparent; color: inherit; border: 1px solid currentColor;">
                                {{ __('Keep existing') }}
                            </button>
                            <button type="submit" name="duplicate_action" value="cancel" class="dash-btn-cancel">
                                {{ __('Cancel import') }}
                            </button>
                        </div>
                        <ul class="dash-import-tips" style="margin-top: 1rem;">
                            <li>{{ __('Replace Existing — update matching animals with values from this file. Primary keys stay the same.') }}</li>
                            <li>{{ __('Keep Existing — leave current records unchanged and only import new animals.') }}</li>
                            <li>{{ __('Cancel Import — discard this upload. Nothing is saved.') }}</li>
                        </ul>
                    @else
                        <div class="dash-form-actions">
                            <button type="submit" name="duplicate_action" value="keep" class="dash-btn-save">
                                {{ __('Import :count new animals', ['count' => $newCount]) }}
                            </button>
                            <button type="submit" name="duplicate_action" value="cancel" class="dash-btn-cancel">
                                {{ __('Cancel import') }}
                            </button>
                        </div>
                    @endif
                    @error('duplicate_action')
                        <p class="dash-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </form>
    </div>
@endsection
