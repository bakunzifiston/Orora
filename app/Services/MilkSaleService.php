<?php

namespace App\Services;

use App\Models\MilkSale;
use App\Models\MilkSaleItem;
use App\Models\MilkSaleLog;
use App\Models\MilkSalePayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MilkSaleService
{
    public function __construct(
        private readonly MilkStorageService $storageService,
    ) {}

    public function create(array $attributes, array $items = []): MilkSale
    {
        return DB::transaction(function () use ($attributes, $items) {
            $sale = MilkSale::create([
                ...$attributes,
                'sale_code' => $this->generateSaleCode($attributes['sold_on'] ?? now()),
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            $this->log($sale, 'created');

            foreach ($items as $itemData) {
                $this->addItem($sale, $itemData);
            }

            $this->recalculateTotal($sale);

            return $sale->fresh(['items', 'payments']);
        });
    }

    public function addItem(MilkSale $sale, array $attributes): MilkSaleItem
    {
        if ($sale->status !== 'draft') {
            throw new \InvalidArgumentException('Items can only be added to draft sales.');
        }

        $lineTotal = isset($attributes['line_total'])
            ? (float) $attributes['line_total']
            : (float) ($attributes['unit_price'] ?? 0) * (float) $attributes['quantity_liters'];

        $item = $sale->items()->create([
            ...$attributes,
            'line_total' => $lineTotal,
        ]);

        $this->recalculateTotal($sale);

        return $item;
    }

    public function confirm(MilkSale $sale): MilkSale
    {
        if ($sale->items()->count() === 0) {
            throw new \InvalidArgumentException('Add at least one sale item before confirming.');
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $this->storageService->deductForSaleItem($item);
            }

            $sale->update(['status' => 'confirmed']);
            $this->log($sale, 'confirmed');

            return $sale->fresh();
        });
    }

    public function addPayment(MilkSale $sale, array $attributes): MilkSalePayment
    {
        $payment = $sale->payments()->create([
            ...$attributes,
            'recorded_by' => auth()->id(),
        ]);

        $this->log($sale, 'payment_recorded', ['amount' => $payment->amount]);

        return $payment;
    }

    public function recalculateTotal(MilkSale $sale): void
    {
        $total = (float) $sale->items()->sum('line_total');
        $sale->update(['total_amount' => $total]);
    }

    public function generateSaleCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "MLS-{$dateKey}-";
        $last = MilkSale::query()
            ->where('sale_code', 'like', $prefix.'%')
            ->orderByDesc('sale_code')
            ->value('sale_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    private function log(MilkSale $sale, string $event, array $meta = []): void
    {
        MilkSaleLog::create([
            'milk_sale_id' => $sale->id,
            'event' => $event,
            'meta' => $meta ?: null,
            'user_id' => auth()->id(),
        ]);
    }
}
