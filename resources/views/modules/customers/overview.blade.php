@extends('layouts.customers-module')

@section('title', __('Customers — Overview'))

@section('customer-content')
    <div class="farm-dash farms-page customers-page">
        @include('modules.partials.header', [
            'title' => __('Customers'),
            'createRoute' => 'customers.create',
            'createLabel' => '+ '. __('Add customer'),
            'secondaryLinks' => [
                [
                    'route' => 'customers.directory',
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
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Total customers') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
                <a href="{{ route('customers.directory', ['status' => 'active']) }}" class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'health'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active']) }}</div>
                    </div>
                </a>
                <a href="{{ route('customers.directory', ['status' => 'active']) }}" class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'expense'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Outstanding') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['outstanding'], 0) }}
                            <span class="farm-kpi__unit">RWF</span>
                        </div>
                    </div>
                </a>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Over credit limit') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['over_limit']) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'chart'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Sales this month') }}</div>
                        <div class="farm-kpi__value">
                            {{ number_format($stats['sales_month'], 0) }}
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
                    @if ($recentCustomers->isEmpty())
                        <p class="dash-empty">{{ __('No customers yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($recentCustomers as $customer)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <a href="{{ route('customers.show', $customer) }}" class="farm-activity__title">{{ $customer->display_name }}</a>
                                        <span class="farm-activity__meta">{{ $customer->customer_code }}</span>
                                    </div>
                                    <span class="farm-activity__count">
                                        {{ number_format($customer->credit?->outstanding_balance ?? 0, 0) }} RWF
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Top customers') }}</h2>
                    </div>
                </header>
                <div class="farm-panel__body">
                    @if ($topCustomers->isEmpty())
                        <p class="dash-empty">{{ __('No sales linked yet.') }}</p>
                    @else
                        <ul class="farm-activity">
                            @foreach ($topCustomers as $customer)
                                <li class="farm-activity__item farm-activity__item--plain farm-activity__item--split">
                                    <div class="farm-activity__body">
                                        <a href="{{ route('customers.show', $customer) }}" class="farm-activity__title">{{ $customer->display_name }}</a>
                                    </div>
                                    <span class="farm-activity__count">
                                        {{ number_format($customer->lifetime_sales ?? 0, 0) }} RWF
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
