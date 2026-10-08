<?php

namespace App\Http\Requests;

use App\Services\TenantContext;
use App\Services\WorkspaceAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class WorkspaceUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && app(WorkspaceAccessService::class)->isWorkspaceAdmin($user);
    }

    public function rules(): array
    {
        $workspaceUser = $this->route('user');
        $tenantId = TenantContext::id();
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')
                    ->where(fn ($q) => $q->where('tenant_id', $tenantId))
                    ->ignore($workspaceUser?->id),
                Rule::unique('tenant_accounts', 'email')
                    ->ignore(
                        $workspaceUser
                            ? \App\Models\TenantAccount::query()->where('email', $workspaceUser->email)->value('id')
                            : null
                    ),
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::defaults(),
                'confirmed',
            ],
            'role' => ['required', Rule::in(array_keys(config('workspace_access.roles', [])))],
            'is_active' => ['sometimes', 'boolean'],
            'permissions' => ['nullable', 'array'],
            'permissions.*.view' => ['sometimes', 'boolean'],
            'permissions.*.create' => ['sometimes', 'boolean'],
            'permissions.*.edit' => ['sometimes', 'boolean'],
            'permissions.*.delete' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active', true),
            'email' => strtolower((string) $this->input('email')),
        ]);
    }
}
