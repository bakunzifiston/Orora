<?php

namespace App\Services;

use App\Models\Feeding;
use App\Models\FeedInventory;
use App\Models\FeedInventoryMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FeedInventoryService
{
    public function recordMovement(
        FeedInventory $inventory,
        string $movementType,
        float $quantity,
        ?string $notes = null,
        ?object $reference = null,
        ?\DateTimeInterface $movedAt = null,
    ): FeedInventoryMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(__('Quantity must be greater than zero.'));
        }

        if (! in_array($movementType, config('modules.feed_movement_types'), true)) {
            throw new InvalidArgumentException(__('Invalid movement type.'));
        }

        $outbound = in_array($movementType, ['consumption', 'adjustment_out'], true);
        $delta = $outbound ? -$quantity : $quantity;

        return DB::transaction(function () use ($inventory, $movementType, $quantity, $delta, $notes, $reference, $movedAt) {
            $inventory = FeedInventory::query()->lockForUpdate()->findOrFail($inventory->id);

            $balance = (float) $inventory->quantity_on_hand + $delta;

            if ($balance < 0) {
                throw new InvalidArgumentException(__('Insufficient stock for this movement.'));
            }

            $inventory->update(['quantity_on_hand' => $balance]);

            return FeedInventoryMovement::create([
                'feed_inventory_id' => $inventory->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'unit' => $inventory->unit,
                'balance_after' => $balance,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
                'moved_at' => $movedAt ?? now(),
            ]);
        });
    }

    public function deductForFeeding(Feeding $feeding): FeedInventoryMovement
    {
        $inventory = $feeding->feedInventory ?? FeedInventory::query()
            ->where('farm_id', $feeding->farm_id)
            ->where('feed_type_id', $feeding->feed_type_id)
            ->firstOrFail();

        if ($inventory->feedType && ! $inventory->feedType->is_active) {
            throw new InvalidArgumentException(__('This feed type is inactive and cannot be consumed.'));
        }

        return $this->recordMovement(
            $inventory,
            'consumption',
            (float) $feeding->quantity,
            $feeding->notes,
            $feeding,
            $feeding->fed_on,
        );
    }

    /**
     * Put stock back from any consumption movements linked to this feeding.
     */
    public function restoreForFeeding(Feeding $feeding): void
    {
        DB::transaction(function () use ($feeding) {
            $movements = $this->consumptionMovementsFor($feeding);

            foreach ($movements as $movement) {
                $inventory = FeedInventory::query()->lockForUpdate()->find($movement->feed_inventory_id);

                if ($inventory) {
                    $inventory->update([
                        'quantity_on_hand' => (float) $inventory->quantity_on_hand + (float) $movement->quantity,
                    ]);
                }

                $movement->delete();
            }
        });
    }

    /**
     * Reverse previous consumption for a feeding, then deduct for its current values.
     */
    public function syncForFeeding(Feeding $feeding): FeedInventoryMovement
    {
        return DB::transaction(function () use ($feeding) {
            $this->restoreForFeeding($feeding);

            $feeding->refresh();
            $feeding->loadMissing(['feedInventory.feedType']);

            return $this->deductForFeeding($feeding);
        });
    }

    public function ensureInventory(int $farmId, int $feedTypeId, string $unit = 'kg'): FeedInventory
    {
        return FeedInventory::query()->firstOrCreate(
            ['farm_id' => $farmId, 'feed_type_id' => $feedTypeId],
            ['quantity_on_hand' => 0, 'unit' => $unit],
        );
    }

    /**
     * @return Collection<int, FeedInventoryMovement>
     */
    private function consumptionMovementsFor(Feeding $feeding): Collection
    {
        return FeedInventoryMovement::query()
            ->where('reference_type', $feeding->getMorphClass())
            ->where('reference_id', $feeding->getKey())
            ->where('movement_type', 'consumption')
            ->lockForUpdate()
            ->get();
    }
}
