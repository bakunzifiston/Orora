@php
    /** @var \App\Models\SaleTransaction|null $sale */
    $status = $status ?? $sale?->payment_status;
    $label = $label ?? ucfirst((string) $status);
    $class = $class ?? ($sale ? $sale->paymentStatusBadgeClass() : config('modules.sale_payment_status_badge_classes.'.$status, 'dash-sale-payment--default'));
@endphp
<span class="dash-badge dash-sale-payment {{ $class }}">{{ $label }}</span>
