@extends('layouts.sales-module')

@section('title', 'Sales — '.$dispatch->dispatch_code)

@section('sales-content')
    @include('modules.partials.header', [
        'title' => $dispatch->dispatch_code,
        'subtitle' => $dispatch->abattoir_name.' · '.ucfirst($dispatch->dispatch_status),
        'backRoute' => 'sales.abattoir',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Dispatched</div>
                <div class="dash-stat-value">{{ $dispatch->dispatch_date->format('M j, Y') }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'livestock'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Animals</div>
                <div class="dash-stat-value accent">{{ $dispatch->total_animals_dispatched }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'animal'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Farm</div>
                <div class="dash-stat-value" style="font-size: 1.1rem;">{{ $dispatch->farm?->name ?? '—' }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'farm'])
        </div>
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Dispatched animals</div>
        @if ($dispatch->animals->isEmpty())
            <p class="dash-empty">No animals on this dispatch.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Tag</th>
                        <th>Live weight</th>
                        <th>Condition</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dispatch->animals as $row)
                        <tr>
                            <td>{{ $row->animal?->tag_number ?? '—' }}</td>
                            <td>{{ $row->live_weight_kg ? number_format($row->live_weight_kg, 1).' kg' : '—' }}</td>
                            <td>{{ ucfirst($row->animal_condition ?? '—') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="dash-panel" style="margin-bottom: 1.25rem;">
        <div class="dash-panel-title">Returns from abattoir</div>
        @if ($dispatch->returns->isEmpty())
            <p class="dash-empty">No returns recorded yet.</p>
        @else
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Animal</th>
                        <th>Cut</th>
                        <th>Weight</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dispatch->returns as $return)
                        <tr>
                            <td>{{ $return->return_date->format('M j, Y') }}</td>
                            <td>{{ $return->animal?->tag_number ?? '—' }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $return->cut_type)) }}</td>
                            <td>{{ number_format($return->cut_weight_kg, 1) }} kg</td>
                            <td>{{ $return->grade ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($dispatch->dispatch_status !== 'returned')
        <div class="dash-panel">
            <div class="dash-panel-title">Record return</div>
            <form method="POST" action="{{ route('sales.abattoir.returns.store', $dispatch) }}" class="dash-form-grid">
                @csrf
                <div class="dash-form-field">
                    <label for="animal_id">Animal <span class="dash-required">*</span></label>
                    <select name="animal_id" id="animal_id" required>
                        <option value="">Select animal</option>
                        @foreach ($dispatch->animals as $row)
                            @if ($row->animal)
                                <option value="{{ $row->animal_id }}">{{ $row->animal->tag_number }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="return_date">Return date <span class="dash-required">*</span></label>
                    <input type="date" name="return_date" id="return_date" value="{{ old('return_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="dash-form-field">
                    <label for="cut_type">Cut type <span class="dash-required">*</span></label>
                    <select name="cut_type" id="cut_type" required>
                        @foreach ($cutTypes as $cut)
                            <option value="{{ $cut }}">{{ ucfirst(str_replace('_', ' ', $cut)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-form-field">
                    <label for="cut_weight_kg">Cut weight (kg) <span class="dash-required">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="cut_weight_kg" id="cut_weight_kg" required>
                </div>
                <div class="dash-form-field">
                    <label for="carcass_weight_kg">Carcass weight (kg)</label>
                    <input type="number" step="0.01" min="0" name="carcass_weight_kg" id="carcass_weight_kg">
                </div>
                <div class="dash-form-field">
                    <label for="grade">Grade</label>
                    <input type="text" name="grade" id="grade" maxlength="10">
                </div>
                <div class="dash-form-field dash-form-field--full">
                    <label for="return_notes">Notes</label>
                    <textarea name="notes" id="return_notes" rows="2"></textarea>
                </div>
                <div class="dash-form-field" style="align-self: end;">
                    <button type="submit" class="dash-btn-save">Record return</button>
                </div>
            </form>
        </div>
    @endif

    @if ($dispatch->saleTransaction)
        <p style="margin-top: 1rem;">
            <a href="{{ route('sales.transactions.show', $dispatch->saleTransaction) }}">View linked meat sale →</a>
        </p>
    @endif
@endsection
