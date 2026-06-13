<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceInquiry extends CentralModel
{
    protected $fillable = [
        'listing_id',
        'buyer_name',
        'buyer_phone',
        'buyer_email',
        'buyer_location',
        'message',
        'status',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class, 'listing_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }
}
