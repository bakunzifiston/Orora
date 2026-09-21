@extends('layouts.employees-module')

@section('title', __('Employees — Directory'))

@section('employee-content')
    <div class="farm-dash farms-page employees-page">
        @include('modules.partials.header', [
            'title' => __('Employees'),
            'createRoute' => 'employees.create',
            'createLabel' => '+ '.__('Add employee'),
            'secondaryLinks' => [
                [
                    'route' => 'employees.export',
                    'params' => request()->query(),
                    'label' => __('Export CSV'),
                    'class' => 'dash-farm-card__btn',
                ],
                [
                    'route' => 'employees.import',
                    'label' => __('Import'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')

        <form method="GET" action="{{ route('employees.directory') }}" class="dash-ops-toolbar farms-page__toolbar" id="employees-filters-form">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field farms-page__search">
                    <label for="filter_q">{{ __('Search') }}</label>
                    <input
                        type="search"
                        name="q"
                        id="filter_q"
                        value="{{ $search }}"
                        placeholder="{{ __('Name, code, phone, ID…') }}"
                        autocomplete="off"
                    >
                </div>
                <div class="dash-ops-field">
                    <label for="filter_role">{{ __('Role') }}</label>
                    <select name="role" id="filter_role" onchange="this.form.submit()">
                        <option value="">{{ __('All roles') }}</option>
                        @foreach (config('modules.employee_job_roles') as $value => $label)
                            <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_status">{{ __('Status') }}</label>
                    <select name="status" id="filter_status" onchange="this.form.submit()">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (config('modules.employee_statuses') as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label for="filter_farm">{{ __('Primary farm') }}</label>
                    <select name="farm" id="filter_farm" onchange="this.form.submit()">
                        <option value="">{{ __('All farms') }}</option>
                        @foreach ($farms as $farm)
                            <option value="{{ $farm->id }}" @selected($farmId == $farm->id)>{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('employees.directory') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total employees') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
                <a href="{{ route('employees.directory', array_filter(['status' => 'active', 'role' => $role ?: null, 'farm' => $farmId, 'q' => $search ?: null])) }}" class="farm-kpi farm-kpi--production {{ $status === 'active' ? 'farm-kpi--selected' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </a>
                <a href="{{ route('employees.directory', array_filter(['status' => 'on_leave', 'role' => $role ?: null, 'farm' => $farmId, 'q' => $search ?: null])) }}" class="farm-kpi farm-kpi--receivable {{ $status === 'on_leave' ? 'farm-kpi--selected' : '' }}">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'movement'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('On leave') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['on_leave']) }}</div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Monthly payroll') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['monthly_payroll'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </div>

                @foreach ($byRole as $row)
                    <a
                        href="{{ route('employees.directory', array_filter(['role' => $row->job_role, 'status' => $status ?: null, 'farm' => $farmId, 'q' => $search ?: null])) }}"
                        class="farm-kpi farm-kpi--stock {{ $role === $row->job_role ? 'farm-kpi--selected' : '' }}"
                    >
                        <div class="farm-kpi__icon" aria-hidden="true">
                            @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                        </div>
                        <div class="farm-kpi__body">
                            <div class="farm-kpi__label">{{ config('modules.employee_job_roles.'.$row->job_role, $row->job_role) }}</div>
                            <div class="farm-kpi__value">{{ number_format($row->total) }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        @if ($employees->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'employee'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No employees match your search or filters.') }}</p>
                    <a href="{{ route('employees.directory') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No employees registered yet.') }}</p>
                    <a href="{{ route('employees.create') }}" class="dash-btn-save">{{ __('Add an employee') }}</a>
                @endif
            </div>
        @else
            <div class="dash-panel employees-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="employees-table">
                        <thead>
                            <tr>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ __('Farm') }}</th>
                                <th>{{ __('Contact') }}</th>
                                <th>{{ __('Salary') }}</th>
                                <th class="employees-table__actions">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($employees as $employee)
                                @php
                                    $statusTone = match ($employee->status) {
                                        'active' => 'ok',
                                        'on_leave' => 'warn',
                                        'terminated', 'inactive' => 'muted',
                                        default => 'muted',
                                    };
                                    $initials = collect(preg_split('/\s+/', trim((string) $employee->display_name)) ?: [])
                                        ->filter()
                                        ->map(fn ($word) => strtoupper(substr($word, 0, 1)))
                                        ->take(2)
                                        ->join('') ?: strtoupper(substr((string) $employee->employee_code, 0, 2));
                                @endphp
                                <tr>
                                    <td>
                                        <div class="employees-table__person">
                                            <span class="employees-table__avatar" aria-hidden="true">{{ $initials }}</span>
                                            <div class="employees-table__person-text">
                                                <a href="{{ route('employees.show', $employee) }}" class="employees-table__name">{{ $employee->display_name }}</a>
                                                <span class="employees-table__meta">
                                                    {{ $employee->employee_code }}
                                                    <span aria-hidden="true">·</span>
                                                    <span class="employees-table__pill employees-table__pill--{{ $statusTone }}">{{ $employee->statusLabel() }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="employees-table__value">{{ $employee->roleLabel() }}</span>
                                    </td>
                                    <td>
                                        @if ($employee->primaryFarm)
                                            <a href="{{ route('farms.show', $employee->primaryFarm) }}" class="employees-table__main-link">{{ $employee->primaryFarm->name }}</a>
                                        @else
                                            <span class="employees-table__meta">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="employees-table__value">{{ $employee->profile?->phone ?: '—' }}</span>
                                    </td>
                                    <td>
                                        @if ($employee->payroll?->base_salary)
                                            <span class="employees-table__value">
                                                {{ number_format($employee->payroll->base_salary, 0) }}
                                                <span class="employees-table__meta">{{ $employee->payroll->currency }}</span>
                                            </span>
                                        @else
                                            <span class="employees-table__meta">—</span>
                                        @endif
                                    </td>
                                    <td class="employees-table__actions">
                                        <div class="employees-table__action-btns">
                                            <a href="{{ route('employees.show', $employee) }}" class="dash-btn-save dash-btn-save--sm">{{ __('View') }}</a>
                                            <a href="{{ route('employees.edit', $employee) }}" class="dash-farm-card__btn">{{ __('Edit') }}</a>
                                            <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm(@js(__('Remove this employee?')));">
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
            <div class="dash-pagination">{{ $employees->links() }}</div>
        @endif
    </div>
@endsection
