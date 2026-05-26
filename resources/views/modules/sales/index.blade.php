@extends('layouts.dashboard')

@section('title', 'Sales')

@section('content')
    @include('modules.partials.header', [
        'title' => 'Sales',
        'subtitle' => 'Livestock and animal sales transactions.',
        'createRoute' => 'sales.create',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($sales->isEmpty())
            <p class="dash-empty">No sales recorded. <a href="{{ route('sales.create') }}">Record a sale</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Buyer</th>
                            <th>Farm</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->sold_on->format('M j, Y') }}</td>
                                <td><strong>{{ $sale->buyer_name }}</strong></td>
                                <td>{{ $sale->farm->name }}</td>
                                <td>{{ $sale->quantity }}</td>
                                <td>
                                    @if ($sale->total_amount)
                                        {{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="dash-badge">{{ ucfirst($sale->payment_status) }}</span></td>
                                <td>
                                    @include('modules.partials.row-actions', [
                                        'model' => $sale,
                                        'editRoute' => 'sales.edit',
                                        'destroyRoute' => 'sales.destroy',
                                    ])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $sales->links() }}</div>
        @endif
    </div>
@endsection
