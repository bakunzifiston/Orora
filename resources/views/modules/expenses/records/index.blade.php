@extends('layouts.expenses-module')

@section('title', 'Expenses — Records')

@section('expense-content')
    @include('modules.partials.header', [
        'title' => 'Expense records',
        'subtitle' => 'All costs: feed, health, farm operations, and general.',
        'createRoute' => 'expenses.records.create',
        'createLabel' => '+ Add expense',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel" style="margin-bottom: 1rem;">
        <form method="GET" class="dash-form-grid" style="padding: 1rem 1.25rem;">
            <div class="dash-form-field">
                <label for="group">Group</label>
                <select name="group" id="group" onchange="this.form.submit()">
                    <option value="">All groups</option>
                    @foreach (config('modules.expense_groups') as $key => $meta)
                        <option value="{{ $key }}" @selected($filterGroup === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="farm_id">Farm</label>
                <select name="farm_id" id="farm_id" onchange="this.form.submit()">
                    <option value="">All farms</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected($filterFarmId == $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="dash-panel">
        @if ($expenses->isEmpty())
            <p class="dash-empty">No expenses yet. <a href="{{ route('expenses.records.create') }}">Add expense</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Group</th>
                            <th>Category</th>
                            <th>Farm</th>
                            <th>Vendor</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenses as $expense)
                            <tr>
                                <td>{{ $expense->expense_date->format('M j, Y') }}</td>
                                <td>{{ $expense->groupLabel() }}</td>
                                <td>{{ $expense->category->name }}</td>
                                <td>{{ $expense->farm?->name ?? '—' }}</td>
                                <td>{{ $expense->vendor?->name ?? '—' }}</td>
                                <td><strong>{{ number_format($expense->amount, 0) }} {{ $expense->currency }}</strong></td>
                                <td>{{ $expense->source_type ? 'Linked' : 'Manual' }}</td>
                                <td>
                                    @if (! $expense->source_type)
                                        @include('modules.partials.row-actions', [
                                            'model' => $expense,
                                            'editRoute' => 'expenses.records.edit',
                                            'destroyRoute' => 'expenses.records.destroy',
                                        ])
                                    @else
                                        <span style="font-size: 0.75rem; color: #808080;">From module</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $expenses->links() }}</div>
        @endif
    </div>
@endsection
