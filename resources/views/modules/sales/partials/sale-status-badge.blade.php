@php
    /** @var \App\Models\SaleTransaction|null $sale */
    $transaction = ($sale ?? null) instanceof \App\Models\SaleTransaction ? $sale : null;
    $saleStatusKey = $saleStatus ?? $transaction?->sale_status;
    $saleStatusLabel = $saleStatusLabel ?? ($transaction
        ? $transaction->statusLabel()
        : config('modules.sale_status_labels.'.$saleStatusKey, ucfirst(str_replace('_', ' ', (string) $saleStatusKey))));
    $saleStatusClass = $saleStatusClass ?? ($transaction
        ? $transaction->statusBadgeClass()
        : config('modules.sale_status_badge_classes.'.$saleStatusKey, 'dash-sale-status--default'));
@endphp
<span class="dash-badge dash-sale-status {{ $saleStatusClass }}">{{ $saleStatusLabel }}</span>
