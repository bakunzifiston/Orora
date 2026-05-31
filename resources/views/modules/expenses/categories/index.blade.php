@extends('layouts.expenses-module')

@section('title', 'Expenses — Categories')

@section('expense-content')
    @include('modules.partials.header', [
        'title' => 'Categories',
        'subtitle' => 'Feed, health, farm operations, and general expense types.',
        'createRoute' => 'expenses.categories.create',
        'createLabel' => '+ Add category',
    ])
    @include('modules.partials.flash')

    @foreach ($categories as $group => $groupCategories)
        <div class="dash-panel" style="margin-bottom: 1rem;">
            <div class="dash-panel-title">{{ config('modules.expense_groups.'.$group.'.label', $group) }}</div>
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Records</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupCategories as $category)
                            <tr>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->code ?? '—' }}</td>
                                <td>{{ $category->expenses_count }}</td>
                                <td>{{ $category->is_active ? 'Active' : 'Inactive' }}</td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $category,
                                        'editRoute' => 'expenses.categories.edit',
                                        'destroyRoute' => 'expenses.categories.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endsection
