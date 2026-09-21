@extends('layouts.customers-module')

@section('title', __('Customers — Directory'))

@section('customer-content')
    <div class="farm-dash farms-page customers-page">
        @include('modules.partials.header', [
            'title' => __('Customers'),
            'createRoute' => 'customers.create',
            'createLabel' => '+ '. __('Add customer'),
            'secondaryLinks' => [
                [
                    'route' => 'customers.export',
                    'params' => request()->query(),
                    'label' => __('Export CSV'),
                    'class' => 'dash-farm-card__btn',
                ],
                [
                    'route' => 'customers.import',
                    'label' => __('Import'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')

        @php
            $filtersActive = filled($filterQuery) || filled($filterType) || filled($filterStatus);
        @endphp

        <form method="GET" action="{{ route('customers.directory') }}" class="dash-ops-toolbar farms-page__toolbar" id="customers-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field farms-page__search">
                    <label for="filter_q">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="q"
                        id="filter_q"
                        value="{{ $filterQuery }}"
                        placeholder="{{ __('Name or code…') }}"
                        autocomplete="off"
                    >
                </div>
                <div class="dash-ops-field">
                    <label for="filter_type">{{ __('Type') }}</label>
                    <select name="type" id="filter_type" onchange="this.form.submit()">
                        <option value="">{{ __('All types') }}</option>
                        @foreach (config('modules.customer_types') as $value => $label)
                            <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_status">{{ __('Status') }}</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.customer_statuses') as $status)
                            <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('customers.directory') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        @if ($customers->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No customers match your search or filters.') }}</p>
                    <a href="{{ route('customers.directory') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No customers registered yet.') }}</p>
                    <a href="{{ route('customers.create') }}" class="dash-btn-save">{{ __('Add a customer') }}</a>
                @endif
            </div>
        @else
            <div class="dash-panel employees-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="employees-table">
                        <thead>
                            <tr>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Outstanding') }}</th>
                                <th>{{ __('Sales') }}</th>
                                <th class="employees-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                @php
                                    $statusTone = match ($customer->status) {
                                        'active' => 'ok',
                                        'inactive', 'blocked' => 'muted',
                                        default => 'muted',
                                    };
                                    $initials = collect(preg_split('/\s+/', trim((string) $customer->display_name)) ?: [])
                                        ->filter()
                                        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                        ->take(2)
                                        ->join('') ?: strtoupper(substr((string) $customer->customer_code, 0, 2));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="employees-table__person">
                                            <span class="employees-table__avatar" aria-hidden="true">{{ $initials }}</span>
                                            <div class="employees-table__person-text">
                                                <a href="{{ route('customers.show', $customer) }}" class="employees-table__name">{{ $customer->display_name }}</a>
                                                <span class="employees-table__meta">
                                                    {{ $customer->customer_code }}
                                                    <span aria-hidden="true">·</span>
                                                    <span class="employees-table__pill employees-table__pill--{{ $statusTone }}">{{ ucfirst($customer->status) }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="employees-table__value">{{ $customer->typeLabel() }}</span>
                                    </td>
                                    <td>
                                        <span class="employees-table__value">
                                            {{ number_format($customer->credit?->outstanding_balance ?? 0, 0) }}
                                            <span class="employees-table__meta">{{ $customer->currency }}</span>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="employees-table__value">{{ $customer->sale_transactions_count }}</span>
                                    </td>
                                    <td class="employees-table__actions">
                                        <div class="employees-table__action-btns">
                                            <a href="{{ route('customers.show', $customer) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                            <a href="{{ route('customers.edit', $customer) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm(@js(__('Remove this customer?')));">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dash-farm-card__btn dash-farm-card__btn--danger">{{ __('Delete') }}</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $customers->links() }}</div>
        @endif
    </div>
@endsection
