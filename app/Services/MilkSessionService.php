<?php

namespace App\Services;

use App\Models\Animal;
use App\Models\Livestock;
use App\Models\MilkRecord;
use App\Models\MilkSession;
use App\Models\MilkStorage;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MilkSessionService
{
    public function __construct(
        private readonly MilkStorageService $storageService,
    ) {}

    public function create(array $attributes): MilkSession
    {
        $this->assertLivestockBelongsToFarm(
            (int) $attributes['livestock_id'],
            (int) $attributes['farm_id'],
        );

        $date = Carbon::parse($attributes['session_date']);

        try {
            return MilkSession::create([
                ...$attributes,
                'session_code' => $this->generateSessionCode($date),
                'status' => 'open',
                'total_yield_liters' => 0,
                'number_of_animals_milked' => 0,
                'average_yield_per_animal' => 0,
                'created_by' => auth()->id(),
            ]);
        } catch (QueryException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new InvalidArgumentException('A milk session already exists for this herd, date, and shift.');
            }

            throw $e;
        }
    }

    public function update(MilkSession $session, array $attributes): MilkSession
    {
        $this->assertSessionEditable($session);

        if (isset($attributes['livestock_id'], $attributes['farm_id'])) {
            $this->assertLivestockBelongsToFarm(
                (int) $attributes['livestock_id'],
                (int) $attributes['farm_id'],
            );
        }

        $session->update($attributes);

        return $session->fresh();
    }

    public function addRecord(MilkSession $session, array $attributes): MilkRecord
    {
        $this->assertSessionEditable($session);

        $animal = Animal::query()->findOrFail($attributes['animal_id']);
        $this->assertAnimalEligible($session, $animal);

        if ($session->records()->where('animal_id', $animal->id)->exists()) {
            throw new InvalidArgumentException('This animal already has a record in this session.');
        }

        return DB::transaction(function () use ($session, $attributes) {
            $record = $session->records()->create([
                ...$attributes,
                'record_code' => $this->generateRecordCode($session->session_date),
                'created_by' => auth()->id(),
            ]);

            $this->recomputeTotals($session);

            return $record;
        });
    }

    /**
     * @param  array<int|string, mixed>  $yieldsByAnimalId
     * @return array{added: int, skipped: int, errors: array<int, string>}
     */
    public function addRecordsBulk(MilkSession $session, array $yieldsByAnimalId, ?string $bulkLines = null): array
    {
        $this->assertSessionEditable($session);

        $pending = [];

        foreach ($yieldsByAnimalId as $animalId => $liters) {
            if ($liters === null || $liters === '') {
                continue;
            }

            $pending[(int) $animalId] = (float) $liters;
        }

        foreach ($this->parseBulkLines($session, $bulkLines) as $animalId => $liters) {
            $pending[$animalId] = $liters;
        }

        $added = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($session, $pending, &$added, &$skipped, &$errors) {
            foreach ($pending as $animalId => $liters) {
                if ($liters <= 0) {
                    $skipped++;

                    continue;
                }

                if ($session->records()->where('animal_id', $animalId)->exists()) {
                    $errors[] = "Animal #{$animalId} already recorded — skipped.";
                    $skipped++;

                    continue;
                }

                try {
                    $animal = Animal::query()->findOrFail($animalId);
                    $this->assertAnimalEligible($session, $animal);

                    $session->records()->create([
                        'animal_id' => $animalId,
                        'yield_liters' => $liters,
                        'record_code' => $this->generateRecordCode($session->session_date),
                        'created_by' => auth()->id(),
                    ]);

                    $added++;
                } catch (\Throwable $e) {
                    $tag = Animal::query()->find($animalId)?->tag_number ?? (string) $animalId;
                    $errors[] = "{$tag}: {$e->getMessage()}";
                    $skipped++;
                }
            }

            $this->recomputeTotals($session);
        });

        return compact('added', 'skipped', 'errors');
    }

    /**
     * @return array<int, float>
     */
    public function parseBulkLines(MilkSession $session, ?string $bulkLines): array
    {
        if (! trim((string) $bulkLines)) {
            return [];
        }

        $animalsByTag = Animal::query()
            ->where('farm_id', $session->farm_id)
            ->where('livestock_id', $session->livestock_id)
            ->milkingEligible()
            ->get()
            ->keyBy(fn (Animal $a) => strtolower(trim($a->tag_number)));

        $parsed = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $bulkLines) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (preg_match('/^([^,\t;]+)\s*[,;\t]\s*([\d.]+)/', $line, $m)) {
                $tag = strtolower(trim($m[1]));
                $liters = (float) $m[2];
            } elseif (preg_match('/^(\S+)\s+([\d.]+)$/', $line, $m)) {
                $tag = strtolower(trim($m[1]));
                $liters = (float) $m[2];
            } else {
                continue;
            }

            $animal = $animalsByTag->get($tag);

            if ($animal) {
                $parsed[(int) $animal->id] = $liters;
            }
        }

        return $parsed;
    }

    public function updateRecord(MilkRecord $record, array $attributes): MilkRecord
    {
        $session = $record->session;
        $this->assertSessionEditable($session);

        if (isset($attributes['animal_id']) && (int) $attributes['animal_id'] !== (int) $record->animal_id) {
            $animal = Animal::query()->findOrFail($attributes['animal_id']);
            $this->assertAnimalEligible($session, $animal);

            if ($session->records()->where('animal_id', $animal->id)->where('id', '!=', $record->id)->exists()) {
                throw new InvalidArgumentException('This animal already has a record in this session.');
            }
        }

        return DB::transaction(function () use ($record, $session, $attributes) {
            $record->update($attributes);
            $this->recomputeTotals($session);

            return $record->fresh();
        });
    }

    public function removeRecord(MilkRecord $record): void
    {
        $session = $record->session;
        $this->assertSessionEditable($session);

        DB::transaction(function () use ($record, $session) {
            $record->delete();
            $this->recomputeTotals($session);
        });
    }

    public function complete(MilkSession $session, ?int $destinationStorageId = null): MilkSession
    {
        if (! $session->isOpen()) {
            throw new InvalidArgumentException('Only open sessions can be completed.');
        }

        if ($session->records()->count() === 0) {
            throw new InvalidArgumentException('Add at least one animal record before completing the session.');
        }

        return DB::transaction(function () use ($session, $destinationStorageId) {
            $session = MilkSession::query()->lockForUpdate()->findOrFail($session->id);
            $this->recomputeTotals($session);

            $storageId = $destinationStorageId ?? $session->destination_storage_id;

            if ($storageId) {
                $storage = MilkStorage::query()->findOrFail($storageId);
                $this->storageService->receiveFromSession($storage, $session);
            }

            $session->update([
                'status' => 'completed',
                'destination_storage_id' => $storageId,
                'completed_at' => now(),
                'completed_by' => auth()->id(),
            ]);

            return $session->fresh(['farm', 'livestock', 'records.animal']);
        });
    }

    public function cancel(MilkSession $session): MilkSession
    {
        if (! $session->isOpen()) {
            throw new InvalidArgumentException('Only open sessions can be cancelled.');
        }

        $session->update(['status' => 'cancelled']);

        return $session->fresh();
    }

    public function recomputeTotals(MilkSession $session): void
    {
        $total = (float) $session->records()->sum('yield_liters');
        $count = $session->records()->count();

        $session->update([
            'total_yield_liters' => $total,
            'number_of_animals_milked' => $count,
            'average_yield_per_animal' => $count > 0 ? round($total / $count, 2) : 0,
        ]);
    }

    public function generateSessionCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "MS-{$dateKey}-";
        $last = MilkSession::query()
            ->where('session_code', 'like', $prefix.'%')
            ->orderByDesc('session_code')
            ->value('session_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    public function generateRecordCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "MR-{$dateKey}-";
        $last = MilkRecord::query()
            ->where('record_code', 'like', $prefix.'%')
            ->orderByDesc('record_code')
            ->value('record_code');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }

    private function assertSessionEditable(MilkSession $session): void
    {
        if (! $session->isOpen()) {
            throw new InvalidArgumentException('This session is no longer open for changes.');
        }
    }

    private function assertLivestockBelongsToFarm(int $livestockId, int $farmId): void
    {
        $belongs = Livestock::query()
            ->whereKey($livestockId)
            ->where('farm_id', $farmId)
            ->exists();

        if (! $belongs) {
            throw new InvalidArgumentException('The selected herd does not belong to this farm.');
        }
    }

    private function assertAnimalEligible(MilkSession $session, Animal $animal): void
    {
        if ((int) $animal->farm_id !== (int) $session->farm_id) {
            throw new InvalidArgumentException('Animal must belong to the session farm.');
        }

        if ((int) $animal->livestock_id !== (int) $session->livestock_id) {
            throw new InvalidArgumentException('Animal must belong to the session herd.');
        }

        if (! $animal->isMilkingEligible()) {
            throw new InvalidArgumentException('Only lactating animals can be milked.');
        }
    }
}
