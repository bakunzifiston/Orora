<?php

namespace App\Http\Controllers\Concerns;

trait ProvidesModuleNavigation
{
    protected function moduleViewData(string $activeNav, array $data = []): array
    {
        return array_merge([
            'navigation' => config('modules.navigation'),
            'navigationGroups' => config('modules.navigation_groups'),
            'activeNav' => $activeNav,
        ], $data);
    }
}
