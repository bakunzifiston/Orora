<?php

namespace App\Models\Central;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Route;

class PlatformRelease extends CentralModel
{
    protected $fillable = [
        'version',
        'title',
        'description',
        'category',
        'released_on',
        'module',
        'link_path',
        'audience',
        'status',
        'published_at',
        'created_by',
        'published_by',
    ];

    protected function casts(): array
    {
        return [
            'released_on' => 'date',
            'published_at' => 'datetime',
        ];
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'platform_release_tenant', 'platform_release_id', 'tenant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'published_by');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published');
    }

    /** Published releases a tenant is allowed to see: everyone's, plus ones targeted at it. */
    public function scopeVisibleToTenant(Builder $query, ?string $tenantId): void
    {
        $query->published()->where(function (Builder $q) use ($tenantId) {
            $q->where('audience', 'all');

            if ($tenantId) {
                $q->orWhere(function (Builder $targeted) use ($tenantId) {
                    $targeted->where('audience', 'selected')
                        ->whereHas('tenants', fn (Builder $t) => $t->whereKey($tenantId));
                });
            }
        });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function categoryLabel(): string
    {
        return config('releases.categories.'.$this->category, ucfirst(str_replace('_', ' ', $this->category)));
    }

    public function categoryTone(): string
    {
        return config('releases.category_tones.'.$this->category, 'muted');
    }

    public function icon(): string
    {
        return config('releases.category_icons.'.$this->category, 'box');
    }

    public function moduleLabel(): ?string
    {
        if (! $this->module) {
            return null;
        }

        return collect(config('workspace_access.modules', []))->firstWhere('key', $this->module)['label']
            ?? ucfirst(str_replace('-', ' ', $this->module));
    }

    /**
     * Where "Explore feature" points: the saved path, otherwise the affected module's main page.
     */
    public function exploreUrl(): ?string
    {
        if ($this->link_path) {
            return url($this->link_path);
        }

        if (! $this->module) {
            return null;
        }

        $item = collect(config('modules.navigation', []))->firstWhere('key', $this->module);
        $route = $item['route'] ?? null;

        return $route && Route::has($route) ? route($route) : null;
    }
}
