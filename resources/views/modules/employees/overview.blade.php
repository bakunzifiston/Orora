@extends('layouts.employees-module')

@section('title', __('Employees — Overview'))

@section('employee-content')
    <div class="farm-dash farms-page employees-page">
        @include('modules.partials.header', [
            'title' => __('Employees'),
            'createRoute' => 'employees.create',
            'createLabel' => '+ '. __('Add employee'),
            'secondaryLinks' => [
                [
                    'route' => 'employees.directory',
                    'label' => __('Directory'),
                    'class' => 'dash-farm-card__btn',
                ],
            ],
        ])
        @include('modules.partials.flash')

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
                <a href="{{ route('employees.directory', ['status' => 'active']) }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </a>
                <a href="{{ route('employees.directory', ['status' => 'on_leave']) }}" class="farm-kpi farm-kpi--receivable">
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
            </div>
        </section>

        <div class="farm-dash__charts farm-dash__charts--2">
            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Recently added') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    @if ($recentEmployees->isEmpty())
                        <p class="dash-empty">{{ __('No employees yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($recentEmployees as $employee)
                                <li class="farm-activity__item farm-activity__item--plain">
                                    <div class="farm-activity__body">
                                        <a href="{{ route('employees.show', $employee) }}" class="farm-activity__title">{{ $employee->display_name }}</a>
                                        <span class="farm-activity__meta">
                                            {{ $employee->employee_code }} · {{ $employee->roleLabel() }}
                                            @if ($employee->primaryFarm)
                                                · {{ $employee->primaryFarm->name }}
                                            @endif
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Active by role') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    @if ($byRole->isEmpty())
                        <p class="dash-empty">{{ __('No active employees.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($byRole as $row)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <a href="{{ route('employees.directory', ['role' => $row->job_role, 'status' => 'active']) }}" class="farm-activity__title">
                                            {{ config('modules.employee_job_roles.'.$row->job_role, $row->job_role) }}
                                        </a>
                                    </div>
                                    <span class="farm-activity__count">{{ $row->total }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>

        @if ($byFarm->isNotEmpty())
            <section class="farm-panel" style="margin-top: 1rem;">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Staff by farm') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    <ul class="farm-activity">
                        @foreach ($byFarm as $row)
                            <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                <div class="farm-activity__body">
                                    <span class="farm-activity__title">{{ $row->farm_name }}</span>
                                </div>
                                <span class="farm-activity__count">{{ $row->total }} {{ __('staff') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </div>
@endsection
