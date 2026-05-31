<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farm extends Model
{
    protected $fillable = [
        'name',
        'registration_number',
        'country',
        'province_code',
        'province',
        'district_code',
        'district',
        'sector_code',
        'sector',
        'cell_code',
        'cell',
        'village_code',
        'village',
        'farm_size_hectares',
        'registration_date',
        'status',
        'ownership_type',
        'owner_first_name',
        'owner_last_name',
        'owner_national_id',
        'contact_phone',
        'contact_email',
        'owner_emergency_contact',
        'organization_name',
        'tax_id',
        'owner_dob',
        'owner_gender',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'farm_size_hectares' => 'decimal:2',
            'registration_date' => 'date',
            'owner_dob' => 'date',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(FarmMember::class);
    }

    public function livestock(): HasMany
    {
        return $this->hasMany(Livestock::class);
    }

    public function animals(): HasMany
    {
        return $this->hasMany(Animal::class);
    }

    public function feedings(): HasMany
    {
        return $this->hasMany(Feeding::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function saleTransactions(): HasMany
    {
        return $this->hasMany(SaleTransaction::class);
    }

    public function getOwnerFullNameAttribute(): string
    {
        return trim("{$this->owner_first_name} {$this->owner_last_name}");
    }

    public function getLocationLabelAttribute(): string
    {
        return collect([$this->village, $this->cell, $this->sector, $this->district, $this->province])
            ->filter()
            ->implode(', ');
    }

    public function requiresOrganizationDetails(): bool
    {
        return in_array($this->ownership_type, ['cooperative', 'company'], true);
    }

    public function requiresMembers(): bool
    {
        return $this->requiresOrganizationDetails();
    }
}
