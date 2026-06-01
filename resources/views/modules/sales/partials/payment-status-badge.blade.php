@php
    /** @var \App\Models\SaleTransaction|null $sale */
    $transaction = ($sale ?? null) instanceof \App\Models\SaleTransaction ? $sale : null;
    $paymentStatusKey = $paymentStatus ?? $transaction?->payment_status;
    $paymentStatusLabel = $paymentStatusLabel ?? ($transaction
        ? $transaction->paymentStatusLabel()
        : config('modules.sale_payment_status_labels.'.$paymentStatusKey, ucfirst((string) $paymentStatusKey)));
    $paymentStatusClass = $paymentStatusClass ?? ($transaction
        ? $transaction->paymentStatusBadgeClass()
        : config('modules.sale_payment_status_badge_classes.'.$paymentStatusKey, 'dash-sale-payment--default'));
@endphp
<span class="dash-badge dash-sale-payment {{ $paymentStatusClass }}">{{ $paymentStatusLabel }}</span>
