<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSaleLog extends TenantModel
{
    protected $fillable = [
        'milk_sale_id',
        'event',
        'meta',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(MilkSale::class, 'milk_sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
