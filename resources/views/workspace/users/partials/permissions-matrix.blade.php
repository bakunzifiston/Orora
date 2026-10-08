@php
    $permissionMap = $permissionMap ?? collect();
    $selectedRole = old('role', isset($workspaceUser) && $workspaceUser->isWorkspaceAdmin()
        ? 'workspace_admin'
        : ($workspaceUser->role ?? 'member'));
@endphp

<article class="farm-panel workspace-permissions" data-permissions-panel @if (in_array($selectedRole, ['workspace_admin', 'owner', 'admin'], true)) hidden @endif>
    <header class="farm-panel__head">
        <div>
            <h2 class="farm-panel__title">{{ __('Module access') }}</h2>
            <p class="farm-panel__desc">{{ __('Turn on View first, then Create, Edit, or Delete as needed.') }}</p>
        </div>
    </header>

    <div class="dash-table-wrap workspace-permissions__wrap">
        <table class="employees-table workspace-permissions__table">
            <thead>
                <tr>
                    <th>{{ __('Module') }}</th>
                    <th class="workspace-permissions__col">{{ __('View') }}</th>
                    <th class="workspace-permissions__col">{{ __('Create') }}</th>
                    <th class="workspace-permissions__col">{{ __('Edit') }}</th>
                    <th class="workspace-permissions__col">{{ __('Delete') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($modules as $module)
                    @php
                        $key = $module['key'];
                        $existing = $permissionMap->get($key);
                        $old = old('permissions.'.$key, []);
                        $defaultView = $key === 'dashboard' && ! $existing && empty($old);
                        $checks = [
                            'view' => array_key_exists('view', $old) ? (bool) $old['view'] : ($existing ? (bool) $existing->can_view : $defaultView),
                            'create' => array_key_exists('create', $old) ? (bool) $old['create'] : (bool) ($existing?->can_create),
                            'edit' => array_key_exists('edit', $old) ? (bool) $old['edit'] : (bool) ($existing?->can_edit),
                            'delete' => array_key_exists('delete', $old) ? (bool) $old['delete'] : (bool) ($existing?->can_delete),
                        ];
                        $actions = $module['actions'] ?? ['view', 'create', 'edit', 'delete'];
                    @endphp
                    <tr>
                        <td>
                            <span class="employees-table__value">{{ __($module['label']) }}</span>
                        </td>
                        @foreach (['view', 'create', 'edit', 'delete'] as $action)
                            <td class="workspace-permissions__col">
                                @if (in_array($action, $actions, true))
                                    <label class="workspace-users-check">
                                        <input
                                            type="checkbox"
                                            name="permissions[{{ $key }}][{{ $action }}]"
                                            value="1"
                                            @checked($checks[$action])
                                            data-perm-action="{{ $action }}"
                                            data-perm-module="{{ $key }}"
                                        >
                                    </label>
                                @else
                                    <span class="employees-table__meta">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</article>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const roleSelect = document.querySelector('[data-role-select]');
    const panel = document.querySelector('[data-permissions-panel]');
    if (!roleSelect || !panel) return;

    const syncRole = () => {
        panel.hidden = ['workspace_admin', 'owner', 'admin'].includes(roleSelect.value);
    };
    roleSelect.addEventListener('change', syncRole);
    syncRole();

    panel.querySelectorAll('input[data-perm-action="view"]').forEach((viewBox) => {
        const syncRow = () => {
            const module = viewBox.dataset.permModule;
            panel.querySelectorAll(`input[data-perm-module="${module}"]:not([data-perm-action="view"])`).forEach((box) => {
                if (!viewBox.checked) box.checked = false;
                box.disabled = !viewBox.checked;
            });
        };
        viewBox.addEventListener('change', syncRow);
        syncRow();
    });
});
</script>
