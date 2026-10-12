<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineMovement;
use App\Models\Treatment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MedicineInventoryService
{
    public function recordMovement(
        Medicine $medicine,
        string $movementType,
        float $quantity,
        ?string $notes = null,
        ?object $reference = null,
        ?\DateTimeInterface $movedAt = null,
    ): MedicineMovement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException(__('Quantity must be greater than zero.'));
        }

        if (! in_array($movementType, config('modules.medicine_movement_types'), true)) {
            throw new InvalidArgumentException(__('Invalid movement type.'));
        }

        $outbound = in_array($movementType, ['adjustment_out', 'consumption'], true);
        $delta = $outbound ? -$quantity : $quantity;

        return DB::transaction(function () use ($medicine, $movementType, $quantity, $delta, $notes, $reference, $movedAt) {
            $medicine = Medicine::query()->lockForUpdate()->findOrFail($medicine->id);

            $balance = (float) $medicine->quantity_on_hand + $delta;

            if ($balance < 0) {
                throw new InvalidArgumentException(__('Insufficient medicine stock for this movement.'));
            }

            $medicine->update(['quantity_on_hand' => $balance]);

            return MedicineMovement::create([
                'medicine_id' => $medicine->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'unit' => $medicine->unit,
                'balance_after' => $balance,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
                'notes' => $notes,
                'moved_at' => $movedAt ?? now(),
                'recorded_by' => auth()->id(),
            ]);
        });
    }

    public function deductForTreatment(Treatment $treatment): void
    {
        if (! $treatment->medicine_id || $treatment->quantity_used === null) {
            return;
        }

        $quantity = (float) $treatment->quantity_used;

        if ($quantity <= 0) {
            return;
        }

        $medicine = Medicine::query()->findOrFail($treatment->medicine_id);

        if ($medicine->isExpired($treatment->start_date)) {
            throw new InvalidArgumentException(__('This medicine has expired and cannot be used for treatments.'));
        }

        if (! $medicine->is_active) {
            throw new InvalidArgumentException(__('This medicine is inactive and cannot be used for treatments.'));
        }

        $this->recordMovement(
            $medicine,
            'consumption',
            $quantity,
            $treatment->notes,
            $treatment,
            $treatment->start_date,
        );
    }

    public function restoreForTreatment(Treatment $treatment): void
    {
        if (! $treatment->medicine_id || $treatment->quantity_used === null) {
            return;
        }

        DB::transaction(function () use ($treatment) {
            $movements = MedicineMovement::query()
                ->where('medicine_id', $treatment->medicine_id)
                ->where('movement_type', 'consumption')
                ->where('reference_type', $treatment::class)
                ->where('reference_id', $treatment->getKey())
                ->get();

            if ($movements->isEmpty()) {
                $quantity = (float) $treatment->quantity_used;

                if ($quantity <= 0) {
                    return;
                }

                $medicine = Medicine::query()->lockForUpdate()->find($treatment->medicine_id);

                if ($medicine) {
                    $medicine->update([
                        'quantity_on_hand' => (float) $medicine->quantity_on_hand + $quantity,
                    ]);
                }

                return;
            }

            foreach ($movements as $movement) {
                $medicine = Medicine::query()->lockForUpdate()->find($movement->medicine_id);

                if ($medicine) {
                    $medicine->update([
                        'quantity_on_hand' => (float) $medicine->quantity_on_hand + (float) $movement->quantity,
                    ]);
                }

                $movement->delete();
            }
        });
    }

    public function syncForTreatment(Treatment $treatment, ?Treatment $previous = null): void
    {
        DB::transaction(function () use ($treatment, $previous) {
            if ($previous) {
                $this->restoreForTreatment($previous);
            }

            $treatment->refresh();
            $this->deductForTreatment($treatment);
        });
    }
}
