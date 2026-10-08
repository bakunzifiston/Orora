<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\BelongsToTenant;
use App\Services\WorkspaceAccessService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'tenant_id', 'role', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToTenant, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class, 'tenant_id', 'tenant_id');
    }

    public function modulePermissions(): HasMany
    {
        return $this->hasMany(WorkspaceModulePermission::class);
    }

    public function isWorkspaceAdmin(): bool
    {
        return app(WorkspaceAccessService::class)->isWorkspaceAdmin($this);
    }

    public function isActiveAccount(): bool
    {
        return app(WorkspaceAccessService::class)->isActive($this);
    }

    public function canAccessModule(string $moduleKey, string $action = 'view'): bool
    {
        return app(WorkspaceAccessService::class)->can($this, $moduleKey, $action);
    }

    public function roleLabel(): string
    {
        if ($this->isWorkspaceAdmin()) {
            return __(config('workspace_access.roles.workspace_admin', 'Workspace admin'));
        }

        return __(config('workspace_access.roles.'.$this->role, ucfirst(str_replace('_', ' ', (string) $this->role))));
    }
}
