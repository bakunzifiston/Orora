@extends('layouts.employees-module')

@section('title', __('Employees — Overview'))

@section('employee-content')
    @include('modules.partials.header', [
        'title' => __('Employees overview'),
        'subtitle' => __('Workforce across all farms — roles, assignments, and payroll.'),
        'createRoute' => 'employees.create',
        'createLabel' => '+ '. __('Add employee'),
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Total employees') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['total']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'employee'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Active') }}</div>
                <div class="dash-stat-value accent">{{ number_format($stats['active']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('On leave') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['on_leave']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'health'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">{{ __('Monthly payroll (est.)') }}</div>
                <div class="dash-stat-value">{{ number_format($stats['monthly_payroll'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Recently added') }}</div>
            @if ($recentEmployees->isEmpty())
                <p class="dash-empty">{{ __('No employees yet.') }}</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($recentEmployees as $employee)
                        <li>
                            <div>
                                <a href="{{ route('employees.show', $employee) }}"><strong>{{ $employee->display_name }}</strong></a>
                                <span style="color:#808080;">{{ $employee->employee_code }}</span>
                            </div>
                            <span>{{ $employee->roleLabel() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">{{ __('Active by role') }}</div>
            @if ($byRole->isEmpty())
                <p class="dash-empty">{{ __('No active employees.') }}</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($byRole as $row)
                        <li>
                            <div>{{ config('modules.employee_job_roles.'.$row->job_role, $row->job_role) }}</div>
                            <span>{{ $row->total }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @if ($byFarm->isNotEmpty())
        <div class="dash-panel" style="margin-top: 1.25rem;">
            <div class="dash-panel-title">{{ __('Staff by farm') }}</div>
            <ul class="dash-health-activity">
                @foreach ($byFarm as $row)
                    <li>
                        <div>{{ $row->farm_name }}</div>
                        <span>{{ $row->total }} {{ __('staff') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
