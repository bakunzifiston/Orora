<?php

namespace App\Models;

use App\Support\TenantStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDocument extends Model
{
    protected $fillable = [
        'sale_transaction_id',
        'document_type',
        'document_number',
        'file_path',
        'generated_by',
        'generated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SaleTransaction::class, 'sale_transaction_id');
    }

    public function fileUrl(): ?string
    {
        return TenantStorageUrl::forPublicDisk($this->file_path);
    }
}
