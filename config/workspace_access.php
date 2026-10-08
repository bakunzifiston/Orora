<?php

/**
 * Tenant workspace module access (roles & permissions).
 *
 * Module keys align with config/modules.php navigation item keys.
 */
return [

    'roles' => [
        'workspace_admin' => 'Workspace admin',
        'member' => 'Member',
    ],

    /**
     * Modules that can be granted to workspace members.
     *
     * @var list<array{key: string, label: string, actions: list<string>}>
     */
    'modules' => [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'actions' => ['view']],
        ['key' => 'farms', 'label' => 'Farms', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'livestock', 'label' => 'Livestock', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'animals', 'label' => 'Animals', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'flocks', 'label' => 'Flocks', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'employees', 'label' => 'Employees', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'health', 'label' => 'Health', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'feeding', 'label' => 'Feeding', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'feed-calculator', 'label' => 'Feed calculator', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'milk', 'label' => 'Milk', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'eggs', 'label' => 'Eggs', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'breeding', 'label' => 'Breeding', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'certificates', 'label' => 'Certificates', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'movement', 'label' => 'Movement', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'sales', 'label' => 'Sales', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'customers', 'label' => 'Customers', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'expenses', 'label' => 'Expenses', 'actions' => ['view', 'create', 'edit', 'delete']],
        ['key' => 'finance', 'label' => 'Finance', 'actions' => ['view', 'create', 'edit', 'delete']],
    ],

    /**
     * Map route-name prefixes to module keys.
     *
     * @var array<string, string>
     */
    'route_modules' => [
        'dashboard' => 'dashboard',
        'farms' => 'farms',
        'livestock' => 'livestock',
        'animals' => 'animals',
        'flocks' => 'flocks',
        'employees' => 'employees',
        'health' => 'health',
        'feeding' => 'feeding',
        'feeding.calculator' => 'feed-calculator',
        'eggs' => 'eggs',
        'milk' => 'milk',
        'breeding' => 'breeding',
        'certificates' => 'certificates',
        'movements' => 'movement',
        'sales' => 'sales',
        'customers' => 'customers',
        'expenses' => 'expenses',
        'finance' => 'finance',
        'workspace.users' => 'workspace-users',
    ],

    /**
     * Always allowed for any active authenticated workspace user.
     *
     * @var list<string>
     */
    'always_allowed_route_prefixes' => [
        'profile',
        'logout',
        'locale',
        'api.rwanda',
        'stancl.tenancy',
    ],
];
