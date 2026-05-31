@extends('layouts.customers-module')

@section('title', 'Customers — Communications')

@section('customer-content')
    @include('modules.partials.header', [
        'title' => 'Communications log',
        'subtitle' => 'Calls, visits, and follow-ups across all customers.',
    ])
    @include('modules.partials.flash')

    <form method="GET" action="{{ route('customers.communications') }}" class="dash-index-toolbar" style="margin-bottom: 1rem;">
        <div class="dash-form-grid" style="align-items: end;">
            <div class="dash-form-field">
                <label for="filter_type">Type</label>
                <select name="type" id="filter_type">
                    <option value="">All types</option>
                    @foreach (config('modules.customer_communication_types') as $value => $label)
                        <option value="{{ $value }}" @selected($filterType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="dash-form-field">
                <label class="dash-checkbox">
                    <input type="checkbox" name="follow_up" value="1" @checked($filterFollowUp)>
                    <span>Pending follow-ups only</span>
                </label>
            </div>
            <div class="dash-form-field">
                <button type="submit" class="dash-btn-save">Filter</button>
            </div>
        </div>
    </form>

    <div class="dash-panel">
        @if ($communications->isEmpty())
            <p class="dash-empty">No communications logged yet.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Subject</th>
                            <th>Summary</th>
                            <th>Follow-up</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($communications as $comm)
                            <tr>
                                <td>{{ $comm->communication_date->format('M j, Y') }}</td>
                                <td><a href="{{ route('customers.show', $comm->customer) }}">{{ $comm->customer->display_name }}</a></td>
                                <td>{{ config('modules.customer_communication_types.'.$comm->communication_type, $comm->communication_type) }}</td>
                                <td>{{ $comm->subject ?? '—' }}</td>
                                <td>{{ Str::limit($comm->summary, 80) }}</td>
                                <td>
                                    @if ($comm->follow_up_required)
                                        {{ $comm->follow_up_date?->format('M j') ?? 'Required' }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $communications->links() }}</div>
        @endif
    </div>
@endsection
