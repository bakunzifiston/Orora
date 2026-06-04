<?php

namespace App\Observers;

use App\Models\MilkRecord;
use App\Services\Milk\MilkCostPerLitreService;

class MilkRecordObserver
{
    public function __construct(private readonly MilkCostPerLitreService $costPerLitre) {}

    public function saved(MilkRecord $record): void
    {
        $this->invalidate($record);
    }

    public function deleted(MilkRecord $record): void
    {
        $this->invalidate($record);
    }

    private function invalidate(MilkRecord $record): void
    {
        $record->loadMissing('session');
        $farmId = $record->session?->farm_id;
        $sessionDate = $record->session?->session_date?->toDateString();

        $this->costPerLitre->invalidateCache($farmId, $sessionDate);
    }
}
