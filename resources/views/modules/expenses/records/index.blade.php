@extends('layouts.expenses-module')

@section('title', __('Expenses — Records'))

@section('expense-content')
    <div class="farm-dash farms-page expenses-page">
        @include('modules.partials.header', [
            'title' => __('Records'),
            'createRoute' => 'expenses.records.create',
            'createLabel' => '+ '.__('Add expense'),
        ])
        @include('modules.partials.flash')

        @php
            $filtersActive = filled($filterGroup) || filled($filterFarmId);
        @endphp

        <form method="GET" action="{{ route('expenses.records') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="group">{{ __('Group') }}</label>
                    <select name="group" id="group" onchange="this.form.submit()">
                        <option value="">{{ __('All groups') }}</option>
                        @foreach (config('modules.expense_groups') as $key => $meta)
                            <option value="{{ $key }}" @selected($filterGroup === $key)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="farm_id">{{ __('Farm') }}</label>
                    <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected($filterFarmId == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('expenses.records') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        @if ($expenses->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No expenses match your filters.') }}</p>
                    <a href="{{ route('expenses.records') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No expenses logged yet.') }}</p>
                    <a href="{{ route('expenses.records.create') }}" class="dash-btn-save">{{ __('Add expense') }}</a>
                @endif
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Expense') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Vendor') }}</th>
                                <th>{{ __('Amount') }}</th>
                                <th>{{ __('Source') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expenses as $expense)
                                @php
                                    $sourceTone = $expense->source_type ? 'muted' : 'ok';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'expense', 'tone' => $sourceTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $expense->category->name }}</span>
                                                <span class="health-table__meta">
                                                    {{ $expense->groupLabel() }}
                                                    <span aria-hidden="true">·</span>
                                                    {{ $expense->expense_date->format('M j, Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $expense->farm?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $expense->vendor?->name ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">
                                            {{ number_format($expense->amount, 0) }}
                                            <span class="health-table__meta">{{ $expense->currency }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="health-table__pill health-table__pill--{{ $sourceTone }}">
                                            {{ $expense->source_type ? __('Linked') : __('Manual') }}
                                        </span>
                                    </td>
                                    <td class="health-table__actions">
                                        @if (! $expense->source_type)
                                            @include('modules.health.partials.table-actions', [
                                                'model' => $expense,
                                                'editRoute' => 'expenses.records.edit',
                                                'destroyRoute' => 'expenses.records.destroy',
                                                'deleteConfirm' => __('Delete this expense?'),
                                            ])
                                        @else
                                            <span class="health-table__meta">{{ __('From module') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection
