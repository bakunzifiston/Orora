<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreedingLog extends TenantModel
{
    protected $fillable = [
        'breeding_record_id',
        'action_type',
        'action_by',
        'action_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'action_date' => 'datetime',
        ];
    }

    public function breedingRecord(): BelongsTo
    {
        return $this->belongsTo(BreedingRecord::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function actionLabel(): string
    {
        return config('modules.breeding_log_action_labels')[$this->action_type] ?? ucfirst(str_replace('_', ' ', $this->action_type));
    }
}
