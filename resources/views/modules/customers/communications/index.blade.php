@extends('layouts.customers-module')

@section('title', __('Customers — Communications'))

@section('customer-content')
    <div class="farm-dash farms-page customers-page">
        @include('modules.partials.header', [
            'title' => __('Communications'),
        ])
        @include('modules.partials.flash')

        @php
            $filtersActive = filled($filterType) || $filterFollowUp;
        @endphp

        <form method="GET" action="{{ route('customers.communications') }}" class="dash-ops-toolbar farms-page__toolbar">
            <div class="dash-ops-toolbar__controls farms-page__filters">
                <div class="dash-ops-field">
                    <label for="filter_type">{{ __('Type') }}</label>
                    <select name="type" id="filter_type" onchange="this.form.submit()">
                        <option value="">{{ __('All types') }}</option>
                        @foreach (config('modules.customer_communication_types') as $value => $label)
                            <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="dash-ops-field">
                    <label class="dash-checkbox" for="filter_follow_up" style="margin-top: 1.4rem;">
                        <input type="checkbox" name="follow_up" id="filter_follow_up" value="1" @checked($filterFollowUp) onchange="this.form.submit()">
                        <span>{{ __('Pending follow-ups only') }}</span>
                    </label>
                </div>
                <button type="submit" class="dash-btn-save dash-ops-apply">{{ __('Apply') }}</button>
                @if ($filtersActive)
                    <a href="{{ route('customers.communications') }}" class="dash-btn-cancel">{{ __('Clear') }}</a>
                @endif
            </div>
        </form>

        @if ($communications->isEmpty())
            <div class="dash-panel dash-entity-empty">
                <div class="dash-entity-empty__icon" aria-hidden="true">
                    @include('layouts.partials.dashboard-nav-icon', ['icon' => 'customer'])
                </div>
                @if ($filtersActive)
                    <p class="dash-empty">{{ __('No communications match your filters.') }}</p>
                    <a href="{{ route('customers.communications') }}" class="dash-btn-cancel">{{ __('Clear filters') }}</a>
                @else
                    <p class="dash-empty">{{ __('No communications logged yet.') }}</p>
                @endif
            </div>
        @else
            <div class="dash-panel health-page__table-panel">
                <div class="dash-table-wrap">
                    <table class="health-table">
                        <thead>
                            <tr>
                                <th>{{ __('Communication') }}</th>
                                <th>{{ __('Customer') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Follow-up') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($communications as $comm)
                                @php
                                    $followTone = $comm->follow_up_required ? 'warn' : 'muted';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="health-table__primary">
                                            @include('modules.health.partials.table-icon', ['icon' => 'customer', 'tone' => $followTone])
                                            <div class="health-table__stack">
                                                <span class="health-table__title">{{ $comm->subject ?? __('Untitled') }}</span>
                                                <span class="health-table__meta">
                                                    {{ $comm->communication_date->format('M j, Y') }}
                                                    @if ($comm->summary)
                                                        <span aria-hidden="true">·</span>
                                                        {{ Str::limit($comm->summary, 60) }}
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('customers.show', $comm->customer) }}" class="health-table__title health-table__title--link">{{ $comm->customer->display_name }}</a>
                                    </td>
                                    <td>
                                        <span class="health-table__value">{{ config('modules.customer_communication_types.'.$comm->communication_type, $comm->communication_type) }}</span>
                                    </td>
                                    <td>
                                        @if ($comm->follow_up_required)
                                            <span class="health-table__pill health-table__pill--warn">
                                                {{ $comm->follow_up_date?->format('M j, Y') ?? __('Required') }}
                                            </span>
                                        @else
                                            <span class="health-table__meta">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="dash-pagination">{{ $communications->links() }}</div>
        @endif
    </div>
@endsection
