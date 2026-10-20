<?php

namespace App\Models;

use App\Models\TenantModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilkDistribution extends TenantModel
{
    protected $fillable = [
        'farm_id',
        'distribution_code',
        'distributed_at',
        'quantity_liters',
        'category',
        'other_use',
        'recipient_type',
        'recipient_name',
        'recipient_phone',
        'animal_id',
        'milk_storage_id',
        'storage_movement_id',
        'sale_transaction_id',
        'sale_item_id',
        'notes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'distributed_at' => 'datetime',
            'quantity_liters' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function animal(): BelongsTo
    {
        return $this->belongsTo(Animal::class);
    }

    public function storage(): BelongsTo
    {
        return $this->belongsTo(MilkStorage::class, 'milk_storage_id');
    }

    public function storageMovement(): BelongsTo
    {
        return $this->belongsTo(MilkStorageMovement::class, 'storage_movement_id');
    }

    public function saleTransaction(): BelongsTo
    {
        return $this->belongsTo(SaleTransaction::class);
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Rows created from a confirmed milk sale are managed in the Sales module. */
    public function isFromSale(): bool
    {
        return $this->category === 'sold' && $this->sale_item_id !== null;
    }

    /** The category label, or the described use for "Other use" records. */
    public function useLabel(): string
    {
        return $this->category === 'other' && filled($this->other_use)
            ? $this->other_use
            : $this->categoryLabel();
    }

    public function categoryLabel(): string
    {
        return config('modules.milk_distribution_category_labels.'.$this->category, ucfirst(str_replace('_', ' ', $this->category)));
    }

    public function recipientTypeLabel(): string
    {
        return config('modules.milk_distribution_recipient_type_labels.'.$this->recipient_type, ucfirst((string) $this->recipient_type));
    }

    public function recipientLabel(): string
    {
        if ($this->category === 'fed_to_calf' && $this->animal) {
            return trim($this->animal->tag_number.' '.($this->animal->name ? '· '.$this->animal->name : ''));
        }

        return $this->recipient_name ?: $this->recipientTypeLabel();
    }
}
