@php
    /** @var \App\Models\SaleTransaction|null $sale */
    $transaction = ($sale ?? null) instanceof \App\Models\SaleTransaction ? $sale : null;
    $status = $status ?? $transaction?->sale_status;
    $label = $label ?? ($transaction ? $transaction->statusLabel() : config('modules.sale_status_labels.'.$status, ucfirst((string) $status)));
    $class = $class ?? ($transaction ? $transaction->statusBadgeClass() : config('modules.sale_status_badge_classes.'.$status, 'dash-sale-status--default'));
@endphp
<span class="dash-badge dash-sale-status {{ $class }}">{{ $label }}</span>
