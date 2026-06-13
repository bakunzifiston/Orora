<?php

namespace App\Services;

use App\Models\DiseaseRecord;
use Carbon\Carbon;

class DiseaseRecordService
{
    public function generateDiseaseCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "DIS-{$dateKey}-";
        $lastCode = DiseaseRecord::query()
            ->where('disease_code', 'like', $prefix.'%')
            ->orderByDesc('disease_code')
            ->value('disease_code');
        $seq = $lastCode ? ((int) substr($lastCode, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }
}
