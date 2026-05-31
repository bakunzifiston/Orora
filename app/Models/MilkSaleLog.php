<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkSaleLog extends Model
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
