<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkspaceModulePermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class WorkspaceAccessService
{
    public const ROLE_ADMIN = 'workspace_admin';

    public const ROLE_MEMBER = 'member';

    /** Legacy role values treated as workspace admin. */
    public const ADMIN_ROLES = ['workspace_admin', 'owner', 'admin'];

    public function schemaReady(): bool
    {
        return Schema::hasTable('users')
            && Schema::hasColumn('users', 'role')
            && Schema::hasColumn('users', 'is_active')
            && Schema::hasTable('workspace_module_permissions');
    }

    /**
     * @return list<array{key: string, label: string, actions: list<string>}>
     */
    public function modules(): array
    {
        return config('workspace_access.modules', []);
    }

    /**
     * @return list<string>
     */
    public function moduleKeys(): array
    {
        return collect($this->modules())->pluck('key')->all();
    }

    public function isWorkspaceAdmin(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->schemaReady()) {
            return true;
        }

        return in_array($user->role ?? self::ROLE_ADMIN, self::ADMIN_ROLES, true);
    }

    public function isActive(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if (! $this->schemaReady()) {
            return true;
        }

        return (bool) ($user->is_active ?? true);
    }

    public function can(?User $user, string $moduleKey, string $action = 'view'): bool
    {
        if (! $user || ! $this->isActive($user)) {
            return false;
        }

        if ($moduleKey === 'profile') {
            return true;
        }

        if ($moduleKey === 'workspace-users') {
            return $this->isWorkspaceAdmin($user);
        }

        if ($this->isWorkspaceAdmin($user)) {
            return true;
        }

        if (! $this->schemaReady()) {
            return true;
        }

        $permission = $user->relationLoaded('modulePermissions')
            ? $user->modulePermissions->firstWhere('module_key', $moduleKey)
            : $user->modulePermissions()->where('module_key', $moduleKey)->first();

        if (! $permission) {
            return false;
        }

        if (in_array($action, ['create', 'edit', 'delete'], true) && ! $permission->can_view) {
            return false;
        }

        return $permission->allows($action);
    }

    public function canAccessRoute(?User $user, ?string $routeName, string $httpMethod = 'GET'): bool
    {
        if (! $user || ! $routeName) {
            return false;
        }

        if (! $this->isActive($user)) {
            return false;
        }

        foreach (config('workspace_access.always_allowed_route_prefixes', []) as $prefix) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                return true;
            }
        }

        if (str_starts_with($routeName, 'workspace.users')) {
            return $this->isWorkspaceAdmin($user);
        }

        $module = $this->moduleForRoute($routeName);
        if (! $module) {
            return $this->isWorkspaceAdmin($user);
        }

        return $this->can($user, $module, $this->actionForRoute($routeName, $httpMethod));
    }

    public function moduleForRoute(string $routeName): ?string
    {
        $map = config('workspace_access.route_modules', []);

        $best = null;
        $bestLen = -1;
        foreach ($map as $prefix => $module) {
            if ($routeName === $prefix || str_starts_with($routeName, $prefix.'.')) {
                $len = strlen($prefix);
                if ($len > $bestLen) {
                    $best = $module;
                    $bestLen = $len;
                }
            }
        }

        if ($best === 'feeding' && str_contains($routeName, 'calculator')) {
            return 'feed-calculator';
        }

        return $best;
    }

    public function actionForRoute(string $routeName, string $httpMethod = 'GET'): string
    {
        $suffix = $this->routeActionSuffix($routeName);

        return match (true) {
            in_array($suffix, ['create', 'store', 'import', 'import.store', 'import.confirm', 'import.execute-replace'], true) => 'create',
            $httpMethod === 'POST' && ! str_ends_with($routeName, '.index') && ! str_ends_with($routeName, '.show') && ! str_ends_with($routeName, '.overview') => 'create',
            in_array($suffix, ['edit', 'update', 'import.confirm-replace'], true) => 'edit',
            in_array($httpMethod, ['PUT', 'PATCH'], true) => 'edit',
            in_array($suffix, ['destroy', 'cancel'], true) || $httpMethod === 'DELETE' => 'delete',
            default => 'view',
        };
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return list<array<string, mixed>>
     */
    public function filterNavigationGroups(array $groups, ?User $user): array
    {
        if (! $user || ! $this->isActive($user)) {
            return [];
        }

        if ($this->isWorkspaceAdmin($user)) {
            return $this->appendUsersNav($groups);
        }

        return collect($groups)
            ->map(function (array $group) use ($user) {
                $group['items'] = collect($group['items'] ?? [])
                    ->filter(fn (array $item) => $this->can($user, (string) ($item['key'] ?? ''), 'view'))
                    ->values()
                    ->all();

                return $group;
            })
            ->filter(fn (array $group) => ($group['items'] ?? []) !== [])
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $groups
     * @return list<array<string, mixed>>
     */
    private function appendUsersNav(array $groups): array
    {
        $exists = collect($groups)->contains(fn (array $g) => ($g['key'] ?? '') === 'workspace');
        if ($exists) {
            return $groups;
        }

        $groups[] = [
            'key' => 'workspace',
            'label' => 'Workspace',
            'items' => [
                [
                    'key' => 'workspace-users',
                    'label' => 'Users & access',
                    'route' => 'workspace.users.index',
                    'icon' => 'employee',
                ],
            ],
        ];

        return $groups;
    }

    /**
     * @param  array<string, array<string, bool|string|int>>  $matrix
     */
    public function syncPermissions(User $user, array $matrix): void
    {
        if (! $this->schemaReady()) {
            return;
        }

        if ($this->isWorkspaceAdmin($user)) {
            WorkspaceModulePermission::query()->where('user_id', $user->id)->delete();

            return;
        }

        $allowedKeys = $this->moduleKeys();

        foreach ($allowedKeys as $moduleKey) {
            $row = $matrix[$moduleKey] ?? [];
            $view = (bool) ($row['view'] ?? false);
            $create = $view && (bool) ($row['create'] ?? false);
            $edit = $view && (bool) ($row['edit'] ?? false);
            $delete = $view && (bool) ($row['delete'] ?? false);

            if (! $view && ! $create && ! $edit && ! $delete) {
                WorkspaceModulePermission::query()
                    ->where('user_id', $user->id)
                    ->where('module_key', $moduleKey)
                    ->delete();

                continue;
            }

            WorkspaceModulePermission::query()->updateOrCreate(
                ['user_id' => $user->id, 'module_key' => $moduleKey],
                [
                    'can_view' => $view,
                    'can_create' => $create,
                    'can_edit' => $edit,
                    'can_delete' => $delete,
                ]
            );
        }

        WorkspaceModulePermission::query()
            ->where('user_id', $user->id)
            ->whereNotIn('module_key', $allowedKeys)
            ->delete();
    }

    /**
     * @return Collection<string, WorkspaceModulePermission>
     */
    public function permissionMap(User $user): Collection
    {
        return $user->modulePermissions()->get()->keyBy('module_key');
    }

    /**
     * First page a user should land on after login.
     */
    public function homeRouteFor(?User $user): string
    {
        if (! $user || ! $this->isActive($user)) {
            return 'login';
        }

        if ($this->can($user, 'dashboard', 'view')) {
            return 'dashboard';
        }

        foreach ($this->modules() as $module) {
            $key = $module['key'] ?? '';
            if ($key === 'dashboard') {
                continue;
            }
            if ($this->can($user, $key, 'view')) {
                $item = collect(config('modules.navigation_groups', []))
                    ->flatMap(fn (array $group) => $group['items'] ?? [])
                    ->firstWhere('key', $key);
                $route = is_array($item) ? ($item['route'] ?? null) : null;

                if (is_string($route) && $route !== '' && \Illuminate\Support\Facades\Route::has($route)) {
                    return $route;
                }
            }
        }

        return 'profile.edit';
    }

    private function routeActionSuffix(string $routeName): string
    {
        $parts = explode('.', $routeName);
        $last = end($parts) ?: '';
        $prev = count($parts) > 1 ? $parts[count($parts) - 2] : '';

        if (in_array($prev, ['import', 'returns'], true)) {
            return $prev.'.'.$last;
        }

        return $last;
    }
}
