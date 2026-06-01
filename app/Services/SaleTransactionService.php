<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\SaleItem;
use App\Models\SaleLog;
use App\Models\SalePayment;
use App\Models\SaleTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleTransactionService
{
    public function __construct(
        private readonly MilkStorageService $storageService,
        private readonly CustomerService $customerService,
    ) {}

    public function createDraft(array $attributes): SaleTransaction
    {
        return DB::transaction(function () use ($attributes) {
            $saleDate = Carbon::parse($attributes['sale_date'] ?? now());

            $transaction = SaleTransaction::create([
                ...$attributes,
                'sale_number' => $this->generateSaleNumber($attributes['sale_type'], $saleDate),
                'sale_status' => 'draft',
                'payment_status' => 'unpaid',
                'created_by' => auth()->id(),
            ]);

            $this->log($transaction, 'created', 'Sale draft created.');

            return $transaction->fresh(['farm', 'customer']);
        });
    }

    public function addItem(SaleTransaction $transaction, array $attributes): SaleItem
    {
        if (! in_array($transaction->sale_status, ['draft'], true)) {
            throw new InvalidArgumentException('Items can only be added to draft sales.');
        }

        $this->assertItemMatchesType($transaction, $attributes);

        $totalPrice = $this->computeLineTotal($transaction, $attributes);

        $item = $transaction->items()->create([
            ...$attributes,
            'total_price' => $totalPrice,
        ]);

        $this->recalculateTotals($transaction);

        return $item;
    }

    public function recalculateTotals(SaleTransaction $transaction): void
    {
        $subtotal = (float) $transaction->items()->sum('total_price');
        $discount = (float) $transaction->discount_amount;
        $tax = (float) $transaction->tax_amount;
        $total = max(0, $subtotal - $discount + $tax);

        $transaction->update([
            'subtotal_amount' => $subtotal,
            'total_amount' => $total,
        ]);

        $this->syncPaymentStatus($transaction->fresh());
    }

    public function syncPaymentStatus(SaleTransaction $transaction): void
    {
        if ($transaction->sale_status === 'cancelled') {
            return;
        }

        $paid = $transaction->totalPaid();
        $total = (float) $transaction->total_amount;

        $status = match (true) {
            $total <= 0 => 'unpaid',
            $paid >= $total => 'paid',
            $paid > 0 => 'partial',
            default => 'unpaid',
        };

        if ($transaction->payment_status !== $status) {
            $transaction->update(['payment_status' => $status]);
        }
    }

    public function confirm(SaleTransaction $transaction): SaleTransaction
    {
        if ($transaction->sale_type !== 'milk_sale') {
            throw new InvalidArgumentException('Confirm is only used for milk sales (stock deduction).');
        }

        if ($transaction->items()->count() === 0) {
            throw new InvalidArgumentException('Add at least one line item before confirming.');
        }

        return DB::transaction(function () use ($transaction) {
            foreach ($transaction->items as $item) {
                $this->storageService->deductForUnifiedSaleItem($item);
            }

            $transaction->update(['sale_status' => 'confirmed']);
            $this->log($transaction, 'confirmed', 'Milk sale confirmed and stock deducted.');
            $this->customerService->syncOutstandingBalanceById($transaction->customer_id);

            return $transaction->fresh(['items', 'payments']);
        });
    }

    public function complete(SaleTransaction $transaction): SaleTransaction
    {
        if ($transaction->items()->count() === 0) {
            throw new InvalidArgumentException('Cannot complete a sale without items.');
        }

        return DB::transaction(function () use ($transaction) {
            if ($transaction->sale_type === 'animal_sale') {
                $this->completeAnimalSale($transaction);
            } elseif ($transaction->sale_type === 'meat_sale') {
                $this->completeMeatSale($transaction);
            } elseif ($transaction->sale_type === 'milk_sale' && $transaction->sale_status === 'draft') {
                $this->confirm($transaction);
            }

            $transaction->update(['sale_status' => 'completed']);
            $this->log($transaction, 'completed', 'Sale marked completed.');
            $this->syncPaymentStatus($transaction->fresh());
            $this->customerService->syncOutstandingBalanceById($transaction->customer_id);

            return $transaction->fresh(['items', 'payments', 'customer']);
        });
    }

    public function cancel(SaleTransaction $transaction): SaleTransaction
    {
        if (in_array($transaction->sale_status, ['completed', 'cancelled'], true)) {
            throw new InvalidArgumentException('This sale cannot be cancelled.');
        }

        $transaction->update(['sale_status' => 'cancelled']);
        $this->log($transaction, 'cancelled', 'Sale cancelled.');

        return $transaction->fresh();
    }

    public function addPayment(SaleTransaction $transaction, array $attributes): SalePayment
    {
        if ($transaction->sale_status === 'cancelled') {
            throw new InvalidArgumentException('Cannot record payments on a cancelled sale.');
        }

        $amount = (float) $attributes['amount_paid'];
        $balance = max(0, $transaction->balanceDue() - $amount);

        $payment = $transaction->payments()->create([
            ...$attributes,
            'customer_id' => $attributes['customer_id'] ?? $transaction->customer_id,
            'payment_reference' => $attributes['payment_reference'] ?? $this->generatePaymentReference(),
            'remaining_balance' => $balance,
        ]);

        $this->log($transaction, 'payment_added', 'Payment of '.number_format($amount, 0).' recorded.');
        $this->syncPaymentStatus($transaction->fresh());
        $this->customerService->syncOutstandingBalanceById($transaction->customer_id);

        return $payment;
    }

    public function generateSaleNumber(string $type, Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = match ($type) {
            'milk_sale' => "MLS-{$dateKey}-",
            'meat_sale' => "MET-{$dateKey}-",
            default => "SL-{$dateKey}-",
        };

        $last = SaleTransaction::query()
            ->where('sale_number', 'like', $prefix.'%')
            ->orderByDesc('sale_number')
            ->value('sale_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    public function generateDispatchCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "ABD-{$dateKey}-";
        $last = \App\Models\AbattoirDispatch::query()
            ->where('dispatch_code', 'like', $prefix.'%')
            ->orderByDesc('dispatch_code')
            ->value('dispatch_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    private function completeAnimalSale(SaleTransaction $transaction): void
    {
        foreach ($transaction->items()->where('item_type', 'animal')->with('animal')->get() as $item) {
            if (! $item->animal_id) {
                continue;
            }

            $animal = $item->animal ?? Animal::query()->find($item->animal_id);

            if (! $animal) {
                continue;
            }

            $this->assertAnimalSellable($animal);

            $animal->update(['lifecycle_status' => 'Sold']);
        }
    }

    private function completeMeatSale(SaleTransaction $transaction): void
    {
        foreach ($transaction->items()->where('item_type', 'meat_cut')->get() as $item) {
            if ($item->abattoir_return_id) {
                \App\Models\AbattoirReturn::query()
                    ->whereKey($item->abattoir_return_id)
                    ->update(['is_sold' => true]);
            }

            if ($item->animal_id) {
                Animal::query()->whereKey($item->animal_id)->update(['lifecycle_status' => 'Sold']);
            }
        }
    }

    public function assertAnimalSellable(Animal $animal): void
    {
        if (strcasecmp((string) $animal->lifecycle_status, 'Active') !== 0) {
            throw new InvalidArgumentException("Animal {$animal->tag_number} is not active and cannot be sold.");
        }

        if (strcasecmp((string) $animal->health_status, 'Healthy') !== 0) {
            throw new InvalidArgumentException("Animal {$animal->tag_number} must be healthy to sell.");
        }
    }

    private function assertItemMatchesType(SaleTransaction $transaction, array $attributes): void
    {
        $itemType = $attributes['item_type'] ?? null;

        $expected = match ($transaction->sale_type) {
            'animal_sale' => 'animal',
            'meat_sale' => 'meat_cut',
            'milk_sale' => 'milk',
            default => null,
        };

        if ($expected && $itemType !== $expected) {
            throw new InvalidArgumentException("Item type must be {$expected} for this sale.");
        }

        if ($itemType === 'animal' && ! empty($attributes['animal_id'])) {
            $animalId = (int) $attributes['animal_id'];

            if ($transaction->items()->where('animal_id', $animalId)->exists()) {
                throw new InvalidArgumentException('This animal is already on this sale.');
            }

            $onAnotherDraft = SaleItem::query()
                ->where('animal_id', $animalId)
                ->where('item_type', 'animal')
                ->where('sale_transaction_id', '!=', $transaction->id)
                ->whereHas('transaction', fn ($q) => $q
                    ->where('sale_type', 'animal_sale')
                    ->where('sale_status', 'draft'))
                ->exists();

            if ($onAnotherDraft) {
                throw new InvalidArgumentException('This animal is already on another in-progress animal sale.');
            }

            $animal = Animal::query()->findOrFail($animalId);
            $this->assertAnimalSellable($animal);

            if ((int) $animal->farm_id !== (int) $transaction->farm_id) {
                throw new InvalidArgumentException('Animal must belong to the sale farm.');
            }
        }
    }

    private function computeLineTotal(SaleTransaction $transaction, array $attributes): float
    {
        if (isset($attributes['total_price'])) {
            return (float) $attributes['total_price'];
        }

        $pricing = $transaction->pricing_method ?? 'per_animal';

        if ($pricing === 'per_kg') {
            $weight = (float) ($attributes['live_weight_kg'] ?? $attributes['carcass_weight_kg'] ?? 0);
            $pricePerKg = (float) ($attributes['price_per_kg'] ?? $attributes['unit_price'] ?? 0);

            if ($weight <= 0) {
                throw new InvalidArgumentException('Live weight is required for per-kg pricing.');
            }

            if ($pricePerKg <= 0) {
                throw new InvalidArgumentException('Price per kg is required for per-kg pricing.');
            }

            return round($weight * $pricePerKg, 2);
        }

        $qty = (float) ($attributes['quantity'] ?? 1);
        $unitPrice = (float) ($attributes['unit_price'] ?? 0);

        return round($qty * $unitPrice, 2);
    }

    private function generatePaymentReference(): string
    {
        return 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999);
    }

    public function log(SaleTransaction $transaction, string $action, ?string $notes = null): void
    {
        SaleLog::create([
            'sale_transaction_id' => $transaction->id,
            'action_type' => $action,
            'action_by' => auth()->id(),
            'action_at' => now(),
            'notes' => $notes,
        ]);
    }
}
