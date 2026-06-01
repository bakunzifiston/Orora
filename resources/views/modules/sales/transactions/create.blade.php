@extends('layouts.sales-module')

@section('title', 'Sales — New transaction')

@section('sales-content')
    @include('modules.partials.header', [
        'title' => 'New sale',
        'subtitle' => config('modules.sale_type_labels.'.$saleType, 'Sale').' — step 1 of 2: sale details',
        'backRoute' => 'sales.transactions',
    ])
    @include('modules.partials.flash')

    <div class="dash-panel" style="margin-bottom: 1rem;">
        <p style="margin: 0; color: #666;">Choose sale type:</p>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.5rem;">
            @foreach (config('modules.sale_type_labels') as $value => $typeLabel)
                <a href="{{ route('sales.transactions.create', ['type' => $value]) }}" class="dash-health-subnav__link {{ $saleType === $value ? 'is-active' : '' }}">{{ $typeLabel }}</a>
            @endforeach
        </div>
    </div>

    <p style="margin: 0 0 1rem; color: #666; font-size: 0.875rem;">
        Enter the farm, customer, and date first. On the next screen you will add what was sold (animals, milk, meat) and confirm the sale.
    </p>

    <form method="POST" action="{{ route('sales.transactions.store') }}" class="dash-farm-form">
        @csrf
        @include('modules.sales.transactions._form', ['saleType' => $saleType])
        <div class="dash-form-actions">
            <button type="submit" class="dash-btn-save">Continue — add items</button>
            <a href="{{ route('sales.transactions') }}" class="dash-btn-cancel">Cancel</a>
        </div>
    </form>
@endsection
