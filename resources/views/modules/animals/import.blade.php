@extends('layouts.dashboard')

@section('title', __('Import animals'))

@section('content')
    <div class="farm-dash farms-page health-page animal-import-page">
        @include('modules.partials.header', [
            'title' => __('Import animals'),
            'backRoute' => 'animals.index',
            'secondaryLinks' => [
                [
                    'route' => 'animals.import.template',
                    'label' => __('Download CSV template'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')

        @if (! empty($pendingImport['preview']))
            <section class="farm-panel animal-import-page__banner">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Pending import') }}</h2>
                        <p class="farm-panel__desc">
                            {{ __('File: :name', ['name' => $pendingImport['original_name'] ?? '—']) }}
                        </p>
                    </div>
                </header>
                <div class="animal-import-page__actions">
                    <a href="{{ route('animals.import.preview') }}" class="dash-btn-save">{{ __('Continue preview') }}</a>
                    <form method="POST" action="{{ route('animals.import.cancel') }}">
                        @csrf
                        <button type="submit" class="dash-farm-card__btn">{{ __('Discard') }}</button>
                    </form>
                </div>
            </section>
        @endif

        <div class="farm-dash__charts farm-dash__charts--2 animal-import-page__grid">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Before you upload') }}</h2>
                        <p class="farm-panel__desc">{{ __('Use the template so columns match Orora.') }}</p>
                    </div>
                </header>
                <ul class="animal-import-page__tips">
                    <li>{{ __('Accepted: .csv, .txt, .xlsx (max 5 MB, 2,000 rows)') }}</li>
                    <li>{{ __('Required: farm_name, livestock_name — farm must already exist') }}</li>
                    <li>{{ __('Duplicates match by tag within each livestock group') }}</li>
                    <li>{{ __('Preview never saves — you confirm before import') }}</li>
                </ul>
                <div class="animal-import-page__actions">
                    <a href="{{ route('animals.import.template') }}" class="dash-btn-save dash-btn-save--sm">{{ __('Download template') }}</a>
                </div>
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Upload file') }}</h2>
                        <p class="farm-panel__desc">{{ __('CSV or Excel. You’ll review a preview next.') }}</p>
                    </div>
                </header>
                <form method="POST" action="{{ route('animals.import.store') }}" enctype="multipart/form-data" class="animal-import-page__form">
                    @csrf
                    <div class="dash-ops-field animal-import-page__file">
                        <label for="file">{{ __('Import file') }} <span class="dash-required">*</span></label>
                        <input
                            type="file"
                            name="file"
                            id="file"
                            accept=".csv,.txt,.xlsx,.xls,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            required
                        >
                        @error('file')
                            <p class="dash-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="animal-import-page__actions">
                        <button type="submit" class="dash-btn-save">{{ __('Upload & preview') }}</button>
                        <a href="{{ route('animals.index') }}" class="dash-farm-card__btn">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </section>
        </div>

        @if (session('import_summary'))
            @php($summary = session('import_summary'))
            <section class="farm-dash__section" aria-label="{{ __('Import summary') }}">
                <div class="farm-dash__kpis farm-dash__kpis--4">
                    <div class="farm-kpi farm-kpi--stock">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Rows in file') }}</div>
                            <div class="farm-kpi__value">{{ number_format($summary['total'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--production">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'animal'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Created') }}</div>
                            <div class="farm-kpi__value">{{ number_format($summary['created'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--receivable">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Updated') }}</div>
                            <div class="farm-kpi__value">{{ number_format($summary['updated'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--sales">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Skipped') }}</div>
                            <div class="farm-kpi__value">{{ number_format($summary['skipped'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div class="farm-kpi farm-kpi--expense">
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'certificate'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ __('Failed') }}</div>
                            <div class="farm-kpi__value">{{ number_format($summary['failed'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if (session('import_warnings'))
            <section class="farm-panel animal-import-page__table-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Rows adjusted') }}</h2>
                        <p class="farm-panel__desc">{{ __('Saved with some values filled in or cleared.') }}</p>
                    </div>
                </header>
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Row') }}</th>
                                <th>{{ __('Adjustment') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (session('import_warnings') as $warning)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'health', 'tone' => 'warn'])
                                            <span class="health-table__value">{{ ($warning['row'] ?? 0) > 0 ? $warning['row'] : '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $warning['message'] ?? '' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        @if (session('import_errors'))
            <section class="farm-panel animal-import-page__table-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Import errors') }}</h2>
                        <p class="farm-panel__desc">{{ __('These rows were not saved.') }}</p>
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
                            @foreach (session('import_errors') as $error)
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
    </div>
@endsection
