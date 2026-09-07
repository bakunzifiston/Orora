<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Animal;
use App\Models\Flock;

trait ProvidesStockOptions
{
    /**
     * @return array{animals: \Illuminate\Support\Collection, flocks: \Illuminate\Support\Collection}
     */
    protected function stockOptions(): array
    {
        return [
            'animals' => Animal::query()->with('farm')->orderBy('tag_number')->get(),
            'flocks' => Flock::query()->with('farm')->orderBy('name')->get(),
        ];
    }
}
