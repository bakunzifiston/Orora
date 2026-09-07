<?php

namespace App\Services;

use App\Models\EggCollection;
use Carbon\Carbon;

class EggCollectionService
{
    public function create(array $attributes): EggCollection
    {
        $date = Carbon::parse($attributes['collected_on'] ?? now());

        return EggCollection::create([
            ...$attributes,
            'shift' => $attributes['shift'] ?: null,
            'collection_code' => $attributes['collection_code'] ?? $this->generateCode($date),
            'cracked_count' => (int) ($attributes['cracked_count'] ?? 0),
        ]);
    }

    public function generateCode(Carbon|string $date): string
    {
        $dateKey = Carbon::parse($date)->format('Ymd');
        $prefix = "EGG-{$dateKey}-";
        $last = EggCollection::query()
            ->where('collection_code', 'like', $prefix.'%')
            ->orderByDesc('collection_code')
            ->value('collection_code');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return sprintf('%s%04d', $prefix, $seq);
    }
}
