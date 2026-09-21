@extends('layouts.admin')

@section('title', __('Marketplace'))

@section('content')
    <div class="farm-dash farms-page health-page admin-dash admin-marketplace">
        <div class="dash-ops-toolbar farms-page__toolbar admin-dash__toolbar">
            <div class="admin-dash__header-text dash-ops-toolbar__brand">
                <h1 class="admin-dash__title">{{ __('Marketplace') }}</h1>
                <p class="admin-dash__period">{{ __('Platform listings across all workspaces.') }}</p>
            </div>
        </div>

        <section class="farm-dash__section" aria-label="{{ __('Summary') }}">
            <div class="farm-dash__kpis farm-dash__kpis--4">
                <div class="farm-kpi farm-kpi--stock">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'box'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Categories') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['categories'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--sales">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Listings') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['listings'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--production">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Active') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['active'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="farm-kpi farm-kpi--receivable">
                    <div class="farm-kpi__icon" aria-hidden="true">
                        @include('layouts.partials.dashboard-nav-icon', ['icon' => 'sale'])
                    </div>
                    <div class="farm-kpi__body">
                        <div class="farm-kpi__label">{{ __('Pending') }}</div>
                        <div class="farm-kpi__value">{{ number_format($stats['pending'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="farm-dash__section" aria-label="{{ __('Listings') }}">
            <article class="farm-panel">
                <header class="farm-panel__head">
                    <div>
                        <h2 class="farm-panel__title">{{ __('Listings') }}</h2>
                        <p class="farm-panel__desc">{{ number_format($listings->total()) }} {{ __('total') }}</p>
                    </div>
                </header>

                @if (empty($shopReady))
                    <p class="dash-empty">{{ __('Marketplace is not set up yet.') }}</p>
                @elseif ($listings->isEmpty())
                    <p class="dash-empty">{{ __('No listings yet.') }}</p>
                @else
                    <div class="dash-table-wrap">
                        <table class="health-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Tenant') }}</th>
                                    <th>{{ __('Price') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listings as $listing)
                                    @php
                                        $statusTone = match ($listing->status) {
                                            'active', 'published' => 'ok',
                                            'pending' => 'warn',
                                            'sold', 'archived' => 'muted',
                                            default => 'muted',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="health-table__primary">
                                                @include('modules.health.partials.table-icon', ['icon' => 'sale', 'tone' => $statusTone])
                                                <span class="health-table__title">{{ $listing->title }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="health-table__value">{{ $listing->category?->name ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__meta">{{ $listing->tenant?->name ?: $listing->tenant_id }}</span>
                                        </td>
                                        <td>
                                            <span class="health-table__value">
                                                {{ number_format((float) $listing->price) }}
                                                <span class="health-table__meta">{{ $listing->currency }}</span>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="health-table__pill health-table__pill--{{ $statusTone }}">{{ $listing->status }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="dash-pagination">{{ $listings->links() }}</div>
                @endif
            </article>
        </section>
    </div>
@endsection

@push('styles')
    @include('central.dashboard.partials.styles')
    <style>
        .admin-marketplace .farm-kpi:not(a) {
            cursor: default;
        }
        .admin-marketplace .farm-kpi:not(a):hover {
            transform: none;
            box-shadow: none;
        }
    </style>
@endpush
