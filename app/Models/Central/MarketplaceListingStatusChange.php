<?php

namespace App\Models\Central;

use App\Models\Central\AdminUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceListingStatusChange extends CentralModel
{
    protected $fillable = [
        'listing_id',
        'admin_user_id',
        'user_id',
        'from_status',
        'to_status',
        'note',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketplaceListing::class, 'listing_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'admin_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
