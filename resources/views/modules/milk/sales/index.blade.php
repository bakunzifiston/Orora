@extends('layouts.milk-module')

@section('title', 'Milk — Sales')

@section('milk-content')
    @include('modules.partials.header', [
        'title' => 'Milk sales',
        'subtitle' => 'Sell milk from storage tanks. Confirm to deduct stock.',
        'createRoute' => 'milk.sales.create',
        'createLabel' => '+ New sale',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel">
        @if ($sales->isEmpty())
            <p class="dash-empty">No milk sales yet. <a href="{{ route('milk.sales.create') }}">Create a sale</a>.</p>
        @else
            <div class="dash-table-wrap">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Code</th>
                            <th>Buyer</th>
                            <th>Farm</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->sold_on->format('M j, Y') }}</td>
                                <td><strong>{{ $sale->sale_code }}</strong></td>
                                <td>{{ $sale->buyer_name }}</td>
                                <td>{{ $sale->farm->name }}</td>
                                <td>{{ number_format($sale->total_amount, 0) }} {{ $sale->currency }}</td>
                                <td><span class="dash-badge">{{ ucfirst($sale->status) }}</span></td>
                                <td><a href="{{ route('milk.sales.edit', $sale) }}" class="dash-btn-link">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="dash-pagination">{{ $sales->links() }}</div>
        @endif
    </div>
@endsection
