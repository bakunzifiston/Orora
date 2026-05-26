<?php

namespace App\Services;

use App\Models\Feeding;
use App\Models\FeedInventory;
use App\Models\FeedInventoryMovement;
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
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (! in_array($movementType, config('modules.feed_movement_types'), true)) {
            throw new InvalidArgumentException('Invalid movement type.');
        }

        $outbound = in_array($movementType, ['consumption', 'adjustment_out'], true);
        $delta = $outbound ? -$quantity : $quantity;

        return DB::transaction(function () use ($inventory, $movementType, $quantity, $delta, $notes, $reference, $movedAt) {
            $inventory = FeedInventory::query()->lockForUpdate()->findOrFail($inventory->id);

            $balance = (float) $inventory->quantity_on_hand + $delta;

            if ($balance < 0) {
                throw new InvalidArgumentException('Insufficient stock for this movement.');
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

        return $this->recordMovement(
            $inventory,
            'consumption',
            (float) $feeding->quantity,
            $feeding->notes,
            $feeding,
            $feeding->fed_on,
        );
    }

    public function ensureInventory(int $farmId, int $feedTypeId, string $unit = 'kg'): FeedInventory
    {
        return FeedInventory::query()->firstOrCreate(
            ['farm_id' => $farmId, 'feed_type_id' => $feedTypeId],
            ['quantity_on_hand' => 0, 'unit' => $unit],
        );
    }
}
