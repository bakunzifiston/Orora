@extends('layouts.expenses-module')

@section('title', __('Expenses — Categories'))

@section('expense-content')
    <div class="farm-dash farms-page expenses-page">
        @include('modules.partials.header', [
            'title' => __('Categories'),
            'createRoute' => 'expenses.categories.create',
            'createLabel' => '+ '.__('Add category'),
        ])
        @include('modules.partials.flash')

        @if ($categories->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                </div>
                <p class="dash-empty">{{ __('No categories yet.') }}</p>
                <a href="{{ route('expenses.categories.create') }}" class="dash-btn-save">{{ __('Add category') }}</a>
            </div>
        @else
            @foreach ($categories as $group => $groupCategories)
                <section class="farm-panel" @if (! $loop->first) style="margin-top: 1rem;" @endif>
                    <header class="farm-panel__head">
                        <div>
                            <h2 class="farm-panel__title">{{ config('modules.expense_groups.'.$group.'.label', $group) }}</h2>
                            <p class="farm-panel__desc">{{ $groupCategories->count() }} {{ __('categories') }}</p>
                        </div>
                    </header>
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Records') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="health-table__actions">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupCategories as $category)
                                    @php
                                        $statusTone = $category->is_active ? 'ok' : 'muted';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'expense', 'tone' => $statusTone])
                                                <div class="health-table__stack">
                                                    <span class="health-table__title">{{ $category->name }}</span>
                                                    <span class="health-table__meta">{{ $category->code ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ $category->expenses_count }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__pill health-table__pill--{{ $statusTone }}">
                                                {{ $category->is_active ? __('Active') : __('Inactive') }}
                                            </span>
                                        </td>
                                        <td class="health-table__actions">
                                            @include('modules.health.partials.table-actions', [
                                                'model' => $category,
                                                'editRoute' => 'expenses.categories.edit',
                                                'destroyRoute' => 'expenses.categories.destroy',
                                                'deleteConfirm' => __('Delete this category?'),
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        @endif
    </div>
@endsection
