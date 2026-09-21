@extends('layouts.expenses-module')

@section('title', __('Expenses — Vendors'))

@section('expense-content')
    <div class="farm-dash farms-page expenses-page">
        @include('modules.partials.header', [
            'title' => __('Vendors'),
            'createRoute' => 'expenses.vendors.create',
            'createLabel' => '+ '.__('Add vendor'),
        ])
        @include('modules.partials.flash')

        @if ($vendors->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                </div>
                <p class="dash-empty">{{ __('No vendors yet.') }}</p>
                <a href="{{ route('expenses.vendors.create') }}" class="dash-btn-save">{{ __('Add vendor') }}</a>
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Vendor') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('Phone') }}</th>
                                <th>{{ __('Expenses') }}</th>
                                <th class="health-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendors as $vendor)
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'customer', 'tone' => 'default'])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $vendor->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vendor->contact_person ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vendor->phone ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ $vendor->expenses_count }}</span>
                                    </td>
                                    <td class="health-table__actions">
                                        @include('modules.health.partials.table-actions', [
                                            'model' => $vendor,
                                            'editRoute' => 'expenses.vendors.edit',
                                            'destroyRoute' => 'expenses.vendors.destroy',
                                            'deleteConfirm' => __('Delete this vendor?'),
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $vendors->links() }}</div>
        @endif
    </div>
@endsection
