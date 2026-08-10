@extends('layouts.sales-module')

@section('title', __('Sales — Abattoir'))

@section('sales-content')
    @include('modules.partials.header', [
        'title' => __('Abattoir dispatches'),
        'subtitle' => __('Send animals for slaughter and record returns.'),
        'createRoute' => 'sales.abattoir.create',
        'createLabel' => '+ '. __('New dispatch'),
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($dispatches->isEmpty())
            <p class="dash-empty">{{ __('No abattoir dispatches yet.') }}</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Farm') }}</th>
                            <th>{{ __('Abattoir') }}</th>
                            <th>{{ __('Animals') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dispatches as $dispatch)
                            <tr>
                                <td><strong>{{ $dispatch->dispatch_code }}</strong></td>
                                <td>{{ $dispatch->dispatch_date->format('M j, Y') }}</td>
                                <td>{{ $dispatch->farm?->name ?? '—' }}</td>
                                <td>{{ $dispatch->abattoir_name }}</td>
                                <td>{{ $dispatch->total_animals_dispatched }}</td>
                                <td>{{ ucfirst($dispatch->dispatch_status) }}</td>
                                <td><a href="{{ route('sales.abattoir.show', $dispatch) }}">{{ __('View') }}</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $dispatches->links() }}</div>
        @endif
    </div>
@endsection
