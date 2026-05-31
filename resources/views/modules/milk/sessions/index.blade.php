@extends('layouts.milk-module')

@section('title', 'Milk — Sessions')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milking sessions',
        'subtitle' => 'One session per herd, date, and shift. Add animal yields, then complete.',
        'createRoute' => 'milk.sessions.create',
        'createLabel' => '+ Open session',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($sessions->isEmpty())
            <p class="dash-empty">No milking sessions yet. <a href="{{ route('milk.sessions.create') }}">Open a session</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Farm / herd</th>
                            <th>Shift</th>
                            <th>Total (L)</th>
                            <th>Animals</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td>{{ $session->session_date->format('M j, Y') }}</td>
                                <td><strong>{{ $session->session_code }}</strong></td>
                                <td>
                                    {{ $session->farm->name }}
                                    <div style="font-size: 0.75rem; color: #808080;">{{ $session->livestock->name }}</div>
                                </td>
                                <td>{{ $session->shiftLabel() }}</td>
                                <td><strong>{{ number_format($session->total_yield_liters, 2) }}</strong></td>
                                <td>{{ $session->number_of_animals_milked }}</td>
                                <td><span class="dash-badge">{{ ucfirst($session->status) }}</span></td>
                                <td>
                                    <a href="{{ route('milk.sessions.edit', $session) }}" class="dash-btn-link">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $sessions->links() }}</div>
        @endif
    </div>
@endsection
