<?php

namespace App\Models\Concerns;

use App\Models\Flock;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToFlock
{
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function stockLabel(): string
    {
        if ($this->flock) {
            return $this->flock->label();
        }

        if (method_exists($this, 'animal') && $this->animal) {
            return collect([$this->animal->tag_number, $this->animal->name])->filter()->implode(' — ');
        }

        return '—';
    }
}
