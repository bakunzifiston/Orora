<?php

namespace App\Http\Controllers\Concerns;

trait ProvidesModuleNavigation
{
    protected function moduleViewData(string $activeNav, array $data = []): array
    {
        return array_merge([
            'navigation' => config('modules.navigation'),
            'activeNav' => $activeNav,
        ], $data);
    }
}
