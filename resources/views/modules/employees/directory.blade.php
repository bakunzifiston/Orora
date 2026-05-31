@extends('layouts.employees-module')

@section('title', 'Employees — Directory')

@section('employee-content')
    @include('modules.partials.header', [
        'title' => 'Employee directory',
        'subtitle' => 'Tenant-wide workforce — assign staff to one or more farms.',
        'createRoute' => 'employees.create',
        'createLabel' => '+ Add employee',
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('employees.directory') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_q">Search</label>
                <input type="search" name="q" id="filter_q" value="{{ $filterQuery }}" placeholder="Name, code, phone, ID">
            </div>
            <div class="dash-form-field">
                <label for="filter_role">Role</label>
                <select name="role" id="filter_role">
                    <option value="">All roles</option>
                    @foreach (config('modules.employee_job_roles') as $value => $label)
                        <option value="{{ $value }}" @selected($filterRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_status">Status</label>
                <select name="status" id="filter_status">
                    <option value="">All statuses</option>
                    @foreach (config('modules.employee_statuses') as $status)
                        <option value="{{ $status }}" @selected($filterStatus === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label for="filter_farm">Primary farm</label>
                <select name="farm" id="filter_farm">
                    <option value="">All farms</option>
                    @foreach ($farms as $farm)
                        <option value="{{ $farm->id }}" @selected((string) $filterFarm === (string) $farm->id)>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">Filter</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($employees->isEmpty())
            <p class="dash-empty">No employees match your filters.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Primary farm</th>
                            <th>Phone</th>
                            <th>Salary</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td>{{ $employee->employee_code }}</td>
                                <td><a href="{{ route('employees.show', $employee) }}"><strong>{{ $employee->display_name }}</strong></a></td>
                                <td>{{ $employee->roleLabel() }}</td>
                                <td>{{ $employee->statusLabel() }}</td>
                                <td>{{ $employee->primaryFarm?->name ?? '—' }}</td>
                                <td>{{ $employee->profile?->phone ?? '—' }}</td>
                                <td>
                                    @if ($employee->payroll?->base_salary)
                                        {{ number_format($employee->payroll->base_salary, 0) }} {{ $employee->payroll->currency }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><a href="{{ route('employees.edit', $employee) }}">Edit</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $employees->links() }}</div>
        @endif
    </div>
@endsection
