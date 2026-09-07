<?php

namespace App\Services;

use App\Models\Flock;
use App\Models\FlockEvent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FlockService
{
    public function create(array $attributes): Flock
    {
        return DB::transaction(function () use ($attributes) {
            $placedOn = Carbon::parse($attributes['placed_on'] ?? now());
            $placedCount = (int) ($attributes['placed_count'] ?? 0);

            $flock = Flock::create([
                ...$attributes,
                'flock_code' => $attributes['flock_code'] ?? $this->generateCode('FLK', $placedOn),
                'current_count' => $attributes['current_count'] ?? $placedCount,
                'lifecycle_status' => $attributes['lifecycle_status'] ?? 'Active',
            ]);

            $this->recordEvent($flock, 'placement', $placedCount, $placedOn, 'Initial placement', $flock);

            return $flock->fresh();
        });
    }

    public function adjust(
        Flock $flock,
        string $eventType,
        int $quantity,
        Carbon|string $occurredOn,
        ?string $notes = null,
        ?Model $reference = null,
    ): Flock {
        return DB::transaction(function () use ($flock, $eventType, $quantity, $occurredOn, $notes, $reference) {
            $next = max(0, $flock->current_count + $quantity);

            $flock->update([
                'current_count' => $next,
                'lifecycle_status' => $next === 0 && $flock->isActive() ? 'Sold' : $flock->lifecycle_status,
            ]);

            $this->recordEvent($flock->fresh(), $eventType, $quantity, $occurredOn, $notes, $reference);

            return $flock->fresh();
        });
    }

    public function recordMortality(Flock $flock, int $deaths, Carbon|string $occurredOn, ?string $notes = null, ?Model $reference = null): Flock
    {
        return $this->adjust($flock, 'mortality', -abs($deaths), $occurredOn, $notes, $reference);
    }

    public function recordSale(Flock $flock, int $sold, Carbon|string $occurredOn, ?string $notes = null, ?Model $reference = null): Flock
    {
        return $this->adjust($flock, 'sale', -abs($sold), $occurredOn, $notes, $reference);
    }

    public function generateCode(string $prefix, Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "{$prefix}-{$dateKey}-";
        $last = Flock::query()
            ->where('flock_code', 'like', $prefix.'%')
            ->orderByDesc('flock_code')
            ->value('flock_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    private function recordEvent(
        Flock $flock,
        string $eventType,
        int $quantity,
        Carbon|string $occurredOn,
        ?string $notes,
        ?Model $reference,
    ): FlockEvent {
        return FlockEvent::create([
            'flock_id' => $flock->id,
            'event_type' => $eventType,
            'quantity' => $quantity,
            'balance_after' => $flock->current_count,
            'occurred_on' => Carbon::parse($occurredOn)->toDateString(),
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'notes' => $notes,
        ]);
    }
}
