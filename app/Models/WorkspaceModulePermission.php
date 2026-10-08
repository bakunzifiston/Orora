<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceModulePermission extends Model
{
    protected $fillable = [
        'user_id',
        'module_key',
        'can_view',
        'can_create',
        'can_edit',
        'can_delete',
    ];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_edit' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allows(string $action): bool
    {
        return match ($action) {
            'view' => (bool) $this->can_view,
            'create' => (bool) $this->can_create,
            'edit' => (bool) $this->can_edit,
            'delete' => (bool) $this->can_delete,
            default => false,
        };
    }
}
