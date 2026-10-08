<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ProvidesModuleNavigation;
use App\Http\Requests\WorkspaceUserRequest;
use App\Models\User;
use App\Models\TenantAccount;
use App\Services\TenantContext;
use App\Services\WorkspaceAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkspaceUserController extends Controller
{
    use ProvidesModuleNavigation;

    public function __construct(
        private readonly WorkspaceAccessService $access,
    ) {}

    public function index(): View
    {
        $base = User::query();

        $stats = [
            'total' => (clone $base)->count(),
            'admins' => (clone $base)->whereIn('role', WorkspaceAccessService::ADMIN_ROLES)->count(),
            'members' => (clone $base)->where('role', WorkspaceAccessService::ROLE_MEMBER)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
        ];

        $users = User::query()
            ->withCount('modulePermissions')
            ->orderByRaw("CASE WHEN role IN ('workspace_admin', 'owner', 'admin') THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->paginate(20);

        return view('workspace.users.index', $this->moduleViewData('workspace-users', [
            'users' => $users,
            'stats' => $stats,
        ]));
    }

    public function create(): View
    {
        return view('workspace.users.create', $this->moduleViewData('workspace-users', [
            'modules' => $this->access->modules(),
            'roles' => config('workspace_access.roles', []),
            'permissionMap' => collect(),
        ]));
    }

    public function store(WorkspaceUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = TenantContext::id();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'password' => $data['password'],
            'tenant_id' => $tenantId,
            'role' => $data['role'],
            'is_active' => true,
        ]);

        // Keep TenantAccount lookup in sync for login routing (email is globally unique).
        if ($tenantId) {
            TenantAccount::query()->firstOrCreate(
                ['email' => strtolower($data['email'])],
                ['tenant_id' => $tenantId],
            );
        }

        if ($data['role'] === WorkspaceAccessService::ROLE_MEMBER) {
            $this->access->syncPermissions($user, $data['permissions'] ?? []);
        }

        return redirect()
            ->route('workspace.users.index')
            ->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View|RedirectResponse
    {
        $this->ensureSameWorkspace($user);

        $user->load('modulePermissions');

        return view('workspace.users.edit', $this->moduleViewData('workspace-users', [
            'workspaceUser' => $user,
            'modules' => $this->access->modules(),
            'roles' => config('workspace_access.roles', []),
            'permissionMap' => $this->access->permissionMap($user),
        ]));
    }

    public function update(WorkspaceUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureSameWorkspace($user);

        $data = $request->validated();
        $admin = auth()->user();

        // Prevent demoting/deactivating the last workspace admin.
        if (
            $user->isWorkspaceAdmin()
            && ($data['role'] !== WorkspaceAccessService::ROLE_ADMIN || ! ($data['is_active'] ?? true))
            && $this->adminCount() <= 1
        ) {
            return back()->withErrors([
                'role' => __('You cannot remove the last workspace admin.'),
            ])->withInput();
        }

        // Admins cannot demote themselves if they are the only admin.
        if ($admin && $admin->id === $user->id && $data['role'] !== WorkspaceAccessService::ROLE_ADMIN) {
            return back()->withErrors([
                'role' => __('You cannot change your own role away from workspace admin.'),
            ])->withInput();
        }

        $payload = [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $previousEmail = $user->email;
        $user->update($payload);

        if ($previousEmail !== $user->email) {
            TenantAccount::query()
                ->where('email', $previousEmail)
                ->where('tenant_id', $user->tenant_id)
                ->update(['email' => $user->email]);
        }

        if ($this->access->isWorkspaceAdmin($user->fresh())) {
            $this->access->syncPermissions($user, []);
        } else {
            $this->access->syncPermissions($user, $data['permissions'] ?? []);
        }

        return redirect()
            ->route('workspace.users.index')
            ->with('success', __('User updated successfully.'));
    }

    public function deactivate(User $user): RedirectResponse
    {
        $this->ensureSameWorkspace($user);

        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => __('You cannot deactivate your own account.')]);
        }

        if ($user->isWorkspaceAdmin() && $this->adminCount() <= 1) {
            return back()->withErrors(['user' => __('You cannot deactivate the last workspace admin.')]);
        }

        $user->update(['is_active' => false]);

        return back()->with('success', __('User deactivated.'));
    }

    public function activate(User $user): RedirectResponse
    {
        $this->ensureSameWorkspace($user);

        $user->update(['is_active' => true]);

        return back()->with('success', __('User activated.'));
    }

    private function ensureSameWorkspace(User $user): void
    {
        $tenantId = TenantContext::id();

        if (! $tenantId || $user->tenant_id !== $tenantId) {
            abort(404);
        }
    }

    private function adminCount(): int
    {
        return User::query()
            ->whereIn('role', WorkspaceAccessService::ADMIN_ROLES)
            ->where('is_active', true)
            ->count();
    }
}
