<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerCommunication extends TenantModel
{
    protected $fillable = [
        'customer_id',
        'communication_type',
        'direction',
        'subject',
        'summary',
        'communication_date',
        'contact_person',
        'follow_up_required',
        'follow_up_date',
        'follow_up_notes',
        'logged_by',
    ];

    protected function casts(): array
    {
        return [
            'communication_date' => 'datetime',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function logger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
