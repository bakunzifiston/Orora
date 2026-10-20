<?php

namespace App\Services\Milk;

use App\Models\MilkDistribution;
use App\Models\MilkStorage;
use App\Models\SaleItem;
use App\Models\SaleTransaction;
use App\Models\User;
use App\Services\MilkStorageService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MilkDistributionService
{
    public function __construct(private readonly MilkStorageService $storage) {}

    /**
     * Record a non-sale use of milk. When a storage tank is given, the litres are
     * deducted from it through the regular storage movement log.
     */
    public function create(array $attributes, ?User $user = null): MilkDistribution
    {
        $this->assertManualCategory($attributes['category'] ?? null);

        return DB::transaction(function () use ($attributes, $user) {
            $distribution = MilkDistribution::create([
                ...$this->normalise($attributes),
                'distribution_code' => $this->generateCode(),
                'recorded_by' => $user?->getKey(),
            ]);

            $this->deductFromStorage($distribution);

            return $distribution->fresh();
        });
    }

    public function update(MilkDistribution $distribution, array $attributes): MilkDistribution
    {
        if ($distribution->isFromSale()) {
            throw new InvalidArgumentException('Sold milk is managed from the Sales module.');
        }

        $this->assertManualCategory($attributes['category'] ?? null);

        return DB::transaction(function () use ($distribution, $attributes) {
            $values = $this->normalise($attributes);

            $stockChanged = (int) ($values['milk_storage_id'] ?? 0) !== (int) $distribution->milk_storage_id
                || round((float) $values['quantity_liters'], 2) !== round((float) $distribution->quantity_liters, 2);

            if ($stockChanged) {
                $this->returnToStorage($distribution);
            }

            $distribution->update($values);

            if ($stockChanged) {
                $this->deductFromStorage($distribution->fresh());
            }

            return $distribution->fresh();
        });
    }

    public function delete(MilkDistribution $distribution): void
    {
        if ($distribution->isFromSale()) {
            throw new InvalidArgumentException('Sold milk is managed from the Sales module.');
        }

        DB::transaction(function () use ($distribution) {
            $this->returnToStorage($distribution);
            $distribution->delete();
        });
    }

    /**
     * Mirror a confirmed milk sale line as a "sold" distribution. Stock was already
     * deducted by the sale, so no storage movement is recorded here.
     */
    public function syncFromSaleItem(SaleItem $item): ?MilkDistribution
    {
        $item->loadMissing('transaction.customer');
        $transaction = $item->transaction;

        if (! $transaction || $item->item_type !== 'milk' || (float) $item->quantity <= 0) {
            return null;
        }

        return MilkDistribution::query()->updateOrCreate(
            ['sale_item_id' => $item->id],
            [
                'farm_id' => $transaction->farm_id,
                'distribution_code' => 'MD-SALE-'.$item->id,
                'distributed_at' => $transaction->sale_date?->copy()->startOfDay() ?? now(),
                'quantity_liters' => $item->quantity,
                'category' => 'sold',
                'recipient_type' => 'customer',
                'recipient_name' => $transaction->customer?->display_name ?: 'Customer',
                'milk_storage_id' => $item->milk_storage_id,
                'sale_transaction_id' => $transaction->id,
                'notes' => "Sale {$transaction->sale_number}",
                'recorded_by' => $transaction->created_by,
            ],
        );
    }

    public function removeForSaleTransaction(SaleTransaction $transaction): void
    {
        MilkDistribution::query()
            ->where('sale_transaction_id', $transaction->id)
            ->where('category', 'sold')
            ->delete();
    }

    /**
     * Litres per category for the given filters, with every category present.
     *
     * @return array<string, float>
     */
    public function totalsByCategory(?int $farmId, ?CarbonInterface $from, ?CarbonInterface $to): array
    {
        $totals = MilkDistribution::query()
            ->when($farmId, fn ($q) => $q->where('farm_id', $farmId))
            ->when($from && $to, fn ($q) => $q->whereBetween('distributed_at', [$from, $to]))
            ->selectRaw('category, SUM(quantity_liters) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return collect(config('modules.milk_distribution_categories', []))
            ->mapWithKeys(fn (string $category) => [$category => round((float) ($totals[$category] ?? 0), 2)])
            ->all();
    }

    public function generateCode(): string
    {
        $prefix = 'MD-'.now()->format('Ymd').'-';
        $last = MilkDistribution::query()
            ->where('distribution_code', 'like', $prefix.'%')
            ->orderByDesc('distribution_code')
            ->value('distribution_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    private function deductFromStorage(MilkDistribution $distribution): void
    {
        if (! $distribution->milk_storage_id) {
            return;
        }

        $storage = MilkStorage::query()->findOrFail($distribution->milk_storage_id);

        $movement = $this->storage->recordMovement(
            $storage,
            'distribution',
            (float) $distribution->quantity_liters,
            "{$distribution->categoryLabel()} · {$distribution->distribution_code}",
            $distribution,
            $distribution->distributed_at,
        );

        $distribution->update(['storage_movement_id' => $movement->id]);
    }

    private function returnToStorage(MilkDistribution $distribution): void
    {
        if (! $distribution->milk_storage_id || ! $distribution->storage_movement_id) {
            return;
        }

        $storage = MilkStorage::query()->find($distribution->milk_storage_id);

        if ($storage) {
            $this->storage->recordMovement(
                $storage,
                'distribution_return',
                (float) $distribution->quantity_liters,
                "Reversed {$distribution->distribution_code}",
                $distribution,
            );
        }

        $distribution->update(['storage_movement_id' => null]);
    }

    private function assertManualCategory(?string $category): void
    {
        if (! in_array($category, config('modules.milk_distribution_manual_categories', []), true)) {
            throw new InvalidArgumentException('Sold milk is recorded through the Sales module.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalise(array $attributes): array
    {
        $isCalf = ($attributes['category'] ?? null) === 'fed_to_calf';

        return [
            'farm_id' => $attributes['farm_id'],
            'distributed_at' => $attributes['distributed_at'],
            'quantity_liters' => $attributes['quantity_liters'],
            'category' => $attributes['category'],
            'other_use' => ($attributes['category'] ?? null) === 'other' ? ($attributes['other_use'] ?? null) : null,
            'recipient_type' => $attributes['recipient_type'],
            'recipient_name' => $isCalf ? null : ($attributes['recipient_name'] ?? null),
            'recipient_phone' => $isCalf ? null : ($attributes['recipient_phone'] ?? null),
            'animal_id' => $isCalf ? ($attributes['animal_id'] ?? null) : null,
            'milk_storage_id' => $attributes['milk_storage_id'] ?? null,
            'notes' => $attributes['notes'] ?? null,
        ];
    }
}
