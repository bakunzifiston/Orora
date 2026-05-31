<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Animal extends Model
{
    protected $fillable = [
        'farm_id',
        'livestock_id',
        'tag_number',
        'name',
        'gender',
        'photo_path',
        'species',
        'breed',
        'date_of_birth',
        'weight_kg',
        'color_markings',
        'acquisition_type',
        'acquisition_date',
        'source',
        'mother_tag',
        'father_tag',
        'health_status',
        'production_status',
        'lifecycle_status',
        'current_condition',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'acquisition_date' => 'date',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function livestock(): BelongsTo
    {
        return $this->belongsTo(Livestock::class);
    }

    public function isMilkingEligible(): bool
    {
        if (strcasecmp((string) $this->production_status, 'Lactating') === 0) {
            return true;
        }

        if (filled($this->production_status)) {
            return false;
        }

        $this->loadMissing('livestock');
        $herdGroups = $this->livestock?->herd_groups ?? [];

        return (bool) array_intersect(config('modules.milking_herd_groups', []), $herdGroups);
    }

    public function scopeMilkingEligible(Builder $query): void
    {
        $milkingHerdGroups = config('modules.milking_herd_groups', []);

        $query->where(function (Builder $q) use ($milkingHerdGroups) {
            $q->where('production_status', 'Lactating');

            if ($milkingHerdGroups !== []) {
                $q->orWhere(function (Builder $q2) use ($milkingHerdGroups) {
                    $q2->where(function (Builder $q3) {
                        $q3->whereNull('production_status')->orWhere('production_status', '');
                    })->whereHas('livestock', function (Builder $lq) use ($milkingHerdGroups) {
                        $lq->where(function (Builder $inner) use ($milkingHerdGroups) {
                            foreach ($milkingHerdGroups as $group) {
                                $inner->orWhereJsonContains('herd_groups', $group);
                            }
                        });
                    });
                });
            }
        });
    }

    public function feedings(): HasMany
    {
        return $this->hasMany(Feeding::class);
    }

    public function milkRecords(): HasMany
    {
        return $this->hasMany(MilkRecord::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }

    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->photo_path) {
                return null;
            }

            return Storage::disk('public')->url($this->photo_path);
        });
    }

    protected function ageLabel(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->date_of_birth) {
                return null;
            }

            $years = (int) $this->date_of_birth->diffInYears(now());
            $months = (int) $this->date_of_birth->copy()->addYears($years)->diffInMonths(now());

            if ($years > 0 && $months > 0) {
                return "{$years}y {$months}m";
            }

            if ($years > 0) {
                return "{$years} year".($years === 1 ? '' : 's');
            }

            if ($months > 0) {
                return "{$months} month".($months === 1 ? '' : 's');
            }

            $days = (int) $this->date_of_birth->diffInDays(now());

            return "{$days} day".($days === 1 ? '' : 's');
        });
    }

    protected function genderLabel(): Attribute
    {
        return Attribute::get(fn () => config('modules.animal_genders')[$this->gender] ?? ucfirst($this->gender));
    }
}
