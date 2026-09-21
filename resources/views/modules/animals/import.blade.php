@extends('layouts.dashboard')

@section('title', __('Import animals'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Import animals'),
        'subtitle' => __('Upload a CSV or Excel file to register multiple animals at once.'),
        'backRoute' => 'animals.index',
    ])
    @include('modules.partials.flash')

    <div class="dash-farm-form">
        @if (! empty($pendingImport['preview']))
            <section class="dash-form-section">
                <header class="dash-form-section__head">
                    <span class="dash-form-section__number" aria-hidden="true">…</span>
                    <div class="dash-form-section__titles">
                        <h2 class="dash-form-section-title">{{ __('Pending import') }}</h2>
                        <p class="dash-form-section-hint">{{ __('You still have a file waiting for confirmation.') }}</p>
                    </div>
                </header>
                <div class="dash-form-section__body">
                    <p class="dash-form-hint" style="margin-bottom: 1rem;">
                        {{ __('File: :name', ['name' => $pendingImport['original_name'] ?? '—']) }}
                    </p>
                    <div class="dash-form-actions">
                        <a href="{{ route('animals.import.preview') }}" class="dash-btn-save">{{ __('Continue preview') }}</a>
                        <form method="POST" action="{{ route('animals.import.cancel') }}">
                            @csrf
                            <button type="submit" class="dash-btn-cancel">{{ __('Discard pending import') }}</button>
                        </form>
                    </div>
                </div>
            </section>
        @endif

        @component('modules.farms._form-section', [
            'number' => '1',
            'title' => __('CSV / Excel template'),
            'description' => __('Start from the template so column names and value formats match Orora.'),
        ])
            <div class="dash-form-actions">
                <a href="{{ route('animals.import.template') }}" class="dash-btn-save">{{ __('Download CSV template') }}</a>
            </div>
            <ul class="dash-import-tips">
                <li>{{ __('Accepted files: .csv, .txt, .xlsx (max 5 MB, 2,000 rows).') }}</li>
                <li>{{ __('Required columns: farm_name, livestock_name. Farm must already exist.') }}</li>
                <li>{{ __('Animals are matched by tag number within each livestock group.') }}</li>
                <li>{{ __('If existing animals are found, you choose Replace, Keep, or Cancel before anything is saved.') }}</li>
                <li>{{ __('Preview never writes to the database. Import runs only after you confirm.') }}</li>
                <li>{{ __('Invalid rows are skipped; valid rows still import. Each failure lists the exact reason.') }}</li>
            </ul>
        @endcomponent

        <form method="POST" action="{{ route('animals.import.store') }}" enctype="multipart/form-data" class="animal-import-form">
            @csrf

            @component('modules.farms._form-section', [
                'number' => '2',
                'title' => __('Upload file'),
                'description' => __('CSV or Excel. You will see a preview before any animals are created or updated.'),
            ])
                <div class="dash-form-grid">
                    <div class="dash-form-field dash-form-field--full">
                        <label for="file">{{ __('Import file') }} <span class="dash-required">*</span></label>
                        <input type="file" name="file" id="file" accept=".csv,.txt,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                        @error('file')
                            <p class="dash-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endcomponent

            <div class="dash-form-section dash-form-section--actions">
                <div class="dash-form-section__body">
                    <div class="dash-form-actions">
                        <button type="submit" class="dash-btn-save">{{ __('Upload & preview') }}</button>
                        <a href="{{ route('animals.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                    </div>
                </div>
            </div>
        </form>

        @if (session('import_summary'))
            @php($summary = session('import_summary'))
            <section class="dash-form-section">
                <header class="dash-form-section__head">
                    <span class="dash-form-section__number" aria-hidden="true">Σ</span>
                    <div class="dash-form-section__titles">
                        <h2 class="dash-form-section-title">{{ __('Import summary') }}</h2>
                        <p class="dash-form-section-hint">{{ __('Counts for this upload only.') }}</p>
                    </div>
                </header>
                <div class="dash-form-section__body">
                    <div class="dash-health-stats">
                        <div class="dash-stat-card">
                            <div class="dash-stat-label">{{ __('Rows in file') }}</div>
                            <div class="dash-stat-value">{{ number_format($summary['total'] ?? 0) }}</div>
                        </div>
                        <div class="dash-stat-card">
                            <div class="dash-stat-label">{{ __('Created') }}</div>
                            <div class="dash-stat-value accent">{{ number_format($summary['created'] ?? 0) }}</div>
                        </div>
                        <div class="dash-stat-card">
                            <div class="dash-stat-label">{{ __('Updated') }}</div>
                            <div class="dash-stat-value">{{ number_format($summary['updated'] ?? 0) }}</div>
                        </div>
                        <div class="dash-stat-card">
                            <div class="dash-stat-label">{{ __('Skipped') }}</div>
                            <div class="dash-stat-value">{{ number_format($summary['skipped'] ?? 0) }}</div>
                        </div>
                        <div class="dash-stat-card">
                            <div class="dash-stat-label">{{ __('Failed') }}</div>
                            <div class="dash-stat-value">{{ number_format($summary['failed'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if (session('import_warnings'))
            <section class="dash-form-section">
                <header class="dash-form-section__head">
                    <span class="dash-form-section__number" aria-hidden="true">i</span>
                    <div class="dash-form-section__titles">
                        <h2 class="dash-form-section-title">{{ __('Rows adjusted on import') }}</h2>
                        <p class="dash-form-section-hint">{{ __('These animals were saved, but some values were filled in or cleared for you.') }}</p>
                    </div>
                </header>
                <div class="dash-form-section__body dash-form-section__body--flush">
                    <div class="dash-table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th class="dash-table__row-col">{{ __('Row') }}</th>
                                    <th>{{ __('Adjustment') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('import_warnings') as $warning)
                                    <tr>
                                        <td>{{ ($warning['row'] ?? 0) > 0 ? $warning['row'] : '—' }}</td>
                                        <td>{{ $warning['message'] ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if (session('import_errors'))
            <section class="dash-form-section">
                <header class="dash-form-section__head">
                    <span class="dash-form-section__number" aria-hidden="true">!</span>
                    <div class="dash-form-section__titles">
                        <h2 class="dash-form-section-title">{{ __('Import errors') }}</h2>
                        <p class="dash-form-section-hint">{{ __('These rows were not saved. Successful rows above were kept.') }}</p>
                    </div>
                </header>
                <div class="dash-form-section__body dash-form-section__body--flush">
                    <div class="dash-table-wrap">
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th class="dash-table__row-col">{{ __('Row') }}</th>
                                    <th>{{ __('Error') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('import_errors') as $error)
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
                </div>
            </section>
        @endif
    </div>
@endsection
