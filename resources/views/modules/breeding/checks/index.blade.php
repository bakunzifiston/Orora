@extends('layouts.breeding-module')

@section('title', 'Breeding — Pregnancy checks')

@section('breeding-content')
    @include('modules.partials.header', [
        'title' => 'Pregnancy checks',
        'subtitle' => 'Confirm or rule out pregnancy after breeding.',
        'createRoute' => 'breeding.checks.create',
        'createLabel' => '+ Add check',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel" style="margin-bottom: 1rem;">
        <form method="GET" class="dash-form-grid" style="padding: 1rem 1.25rem;">
            <div class="dash-form-field">
                <label for="result">Result</label>
                <select name="result" id="result" onchange="this.form.submit()">
                    <option value="">All results</option>
                    @foreach (config('modules.pregnancy_check_results') as $result)
                        <option value="{{ $result }}" @selected(request('result') === $result)>{{ config('modules.pregnancy_check_result_labels')[$result] }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="dash-panel">
        @if ($checks->isEmpty())
            <p class="dash-empty">No pregnancy checks yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date</th>
                            <th>Female</th>
                            <th>Breeding</th>
                            <th>Method</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($checks as $check)
                            <tr>
                                <td>{{ $check->check_code }}</td>
                                <td>{{ $check->check_date->format('M j, Y') }}</td>
                                <td>{{ $check->animal->tag_number }}</td>
                                <td><a href="{{ route('breeding.records.edit', $check->breedingRecord) }}">{{ $check->breedingRecord->breeding_code }}</a></td>
                                <td>{{ $check->methodLabel() }}</td>
                                <td>{{ $check->resultLabel() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $checks->links() }}</div>
        @endif
    </div>
@endsection
