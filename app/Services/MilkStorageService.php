<?php

namespace App\Services;

use App\Models\MilkSaleItem;
use App\Models\MilkSession;
use App\Models\MilkStorage;
use App\Models\MilkStorageMovement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MilkStorageService
{
    public function create(array $attributes): MilkStorage
    {
        return MilkStorage::create([
            ...$attributes,
            'storage_code' => $this->generateStorageCode(),
            'current_quantity_liters' => 0,
            'status' => 'available',
        ]);
    }

    public function recordMovement(
        MilkStorage $storage,
        string $movementType,
        float $quantityLiters,
        ?string $notes = null,
        ?object $reference = null,
        ?\DateTimeInterface $movedAt = null,
    ): MilkStorageMovement {
        if ($quantityLiters <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }

        if (! in_array($movementType, config('modules.milk_storage_movement_types'), true)) {
            throw new InvalidArgumentException('Invalid movement type.');
        }

        $outbound = in_array($movementType, ['sale', 'adjustment_out', 'spoilage'], true);
        $delta = $outbound ? -$quantityLiters : $quantityLiters;

        return DB::transaction(function () use ($storage, $movementType, $quantityLiters, $delta, $notes, $reference, $movedAt) {
            $storage = MilkStorage::query()->lockForUpdate()->findOrFail($storage->id);
            $balance = (float) $storage->current_quantity_liters + $delta;

            if ($balance < 0) {
                throw new InvalidArgumentException('Insufficient milk in storage.');
            }

            $storage->update(['current_quantity_liters' => $balance]);
            $this->refreshStatus($storage);

            return MilkStorageMovement::create([
                'milk_storage_id' => $storage->id,
                'movement_type' => $movementType,
                'quantity_liters' => $quantityLiters,
                'balance_after' => $balance,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
                'moved_at' => $movedAt ?? now(),
            ]);
        });
    }

    public function receiveFromSession(MilkStorage $storage, MilkSession $session): MilkStorageMovement
    {
        if ((int) $storage->farm_id !== (int) $session->farm_id) {
            throw new InvalidArgumentException('Storage tank must belong to the same farm as the session.');
        }

        $liters = (float) $session->total_yield_liters;

        if ($liters <= 0) {
            throw new InvalidArgumentException('Session has no milk to store.');
        }

        return $this->recordMovement(
            $storage,
            'intake',
            $liters,
            "Intake from session {$session->session_code}",
            $session,
            $session->session_date,
        );
    }

    public function deductForSaleItem(MilkSaleItem $item): MilkStorageMovement
    {
        if (! $item->milk_storage_id) {
            throw new InvalidArgumentException('Sale item must specify a storage tank.');
        }

        $storage = MilkStorage::query()->findOrFail($item->milk_storage_id);

        return $this->recordMovement(
            $storage,
            'sale',
            (float) $item->quantity_liters,
            "Sale {$item->sale?->sale_code}",
            $item,
            $item->sale?->sold_on,
        );
    }

    public function refreshStatus(MilkStorage $storage): void
    {
        if ($storage->status === 'maintenance') {
            return;
        }

        $qty = (float) $storage->current_quantity_liters;
        $cap = (float) $storage->capacity_liters;

        $status = match (true) {
            $qty <= 0 => 'available',
            $qty >= $cap => 'full',
            default => 'in_use',
        };

        if ($storage->status !== $status) {
            $storage->update(['status' => $status]);
        }
    }

    public function generateStorageCode(): string
    {
        $prefix = 'MK-'.now()->format('Ymd').'-';
        $last = MilkStorage::query()
            ->where('storage_code', 'like', $prefix.'%')
            ->orderByDesc('storage_code')
            ->value('storage_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }
}
