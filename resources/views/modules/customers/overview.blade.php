@extends('layouts.customers-module')

@section('title', 'Customers — Overview')

@section('customer-content')
    @include('modules.partials.header', [
        'title' => 'Customers overview',
        'subtitle' => 'Tenant-wide customer relationships, credit, and sales history.',
        'createRoute' => 'customers.create',
        'createLabel' => '+ Add customer',
    ])
    @include('modules.partials.flash')

    <div class="dash-health-stats" style="margin-bottom: 1.25rem;">
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Total customers</div>
                <div class="dash-stat-value">{{ number_format($stats['total']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'customer'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Active</div>
                <div class="dash-stat-value accent">{{ number_format($stats['active']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'chart'])
        </div>
        <a href="{{ route('customers.directory', ['status' => 'active']) }}" class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Outstanding balances</div>
                <div class="dash-stat-value">{{ number_format($stats['outstanding'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'expense'])
        </a>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Over credit limit</div>
                <div class="dash-stat-value">{{ number_format($stats['over_limit']) }}</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
        <div class="dash-stat-card">
            <div>
                <div class="dash-stat-label">Sales this month</div>
                <div class="dash-stat-value">{{ number_format($stats['sales_month'], 0) }} RWF</div>
            </div>
            @include('modules.partials.stat-icon', ['icon' => 'sale'])
        </div>
    </div>

    <div class="dash-health-grid">
        <div class="dash-panel">
            <div class="dash-panel-title">Recently added</div>
            @if ($recentCustomers->isEmpty())
                <p class="dash-empty">No customers yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($recentCustomers as $customer)
                        <li>
                            <div>
                                <a href="{{ route('customers.show', $customer) }}"><strong>{{ $customer->display_name }}</strong></a>
                                <span style="color:#808080;">{{ $customer->customer_code }}</span>
                            </div>
                            <span>{{ number_format($customer->credit?->outstanding_balance ?? 0, 0) }} RWF due</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
        <div class="dash-panel">
            <div class="dash-panel-title">Top customers (lifetime)</div>
            @if ($topCustomers->isEmpty())
                <p class="dash-empty">No sales linked yet.</p>
            @else
                <ul class="dash-health-activity">
                    @foreach ($topCustomers as $customer)
                        <li>
                            <div>
                                <a href="{{ route('customers.show', $customer) }}"><strong>{{ $customer->display_name }}</strong></a>
                            </div>
                            <span>{{ number_format($customer->lifetime_sales ?? 0, 0) }} RWF</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
