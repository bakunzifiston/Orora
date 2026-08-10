@extends('layouts.dashboard')

@section('title', __('Import animals'))

@section('content')
    @include('modules.partials.header', [
        'title' => __('Import animals'),
        'subtitle' => __('Upload a CSV file to register multiple animals at once.'),
        'backRoute' => 'animals.index',
    ])
    @include('modules.partials.flash')

    <div class="dash-farm-form">
        @component('modules.farms._form-section', [
            'number' => '1',
            'title' => __('CSV template'),
            'description' => __('Start from the template so column names and value formats match Orora.'),
        ])
            <div class="dash-form-actions">
                <a href="{{ route('animals.import.template') }}" class="dash-btn-save">{{ __('Download CSV template') }}</a>
            </div>
            <ul class="dash-import-tips">
                <li>{{ __('Farm and livestock group names must already exist.') }}</li>
                <li>{{ __('Gender: male, female, or unknown.') }}</li>
                <li>{{ __('Photos are added later on each animal.') }}</li>
            </ul>
        @endcomponent

        <form method="POST" action="{{ route('animals.import.store') }}" enctype="multipart/form-data" class="animal-import-form">
            @csrf

            @component('modules.farms._form-section', [
                'number' => '2',
                'title' => __('Upload file'),
                'description' => __('Maximum 2 MB and 2,000 rows. Invalid rows are skipped and listed below.'),
            ])
                <div class="dash-form-grid">
                    <div class="dash-form-field dash-form-field--full">
                        <label for="file">{{ __('CSV file') }} <span class="dash-required">*</span></label>
                        <input type="file" name="file" id="file" accept=".csv,text/csv" required>
                    </div>
                </div>
            @endcomponent

            <div class="dash-form-section dash-form-section--actions">
                <div class="dash-form-section__body">
                    <div class="dash-form-actions">
                        <button type="submit" class="dash-btn-save">{{ __('Import animals') }}</button>
                        <a href="{{ route('animals.index') }}" class="dash-btn-cancel">{{ __('Cancel') }}</a>
                    </div>
                </div>
            </div>
        </form>

        @if (session('import_errors'))
            <section class="dash-form-section">
                <header class="dash-form-section__head">
                    <span class="dash-form-section__number" aria-hidden="true">!</span>
                    <div class="dash-form-section__titles">
                        <h2 class="dash-form-section-title">{{ __('Import errors') }}</h2>
                        <p class="dash-form-section-hint">{{ __('Fix these rows and import again. Successful rows were already saved.') }}</p>
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
                                        <td>{{ $error['message'] ?? '' }}</td>
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
