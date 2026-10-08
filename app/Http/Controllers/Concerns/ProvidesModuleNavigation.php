<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Species\SpeciesProfile;
use App\Services\WorkspaceAccessService;

trait ProvidesModuleNavigation
{
    protected function moduleViewData(string $activeNav, array $data = []): array
    {
        $species = app(SpeciesProfile::class);
        $access = app(WorkspaceAccessService::class);
        $user = auth()->user();
        $groups = $access->filterNavigationGroups($species->navigationGroups(), $user);

        return array_merge([
            'navigation' => collect($groups)->flatMap(fn (array $g) => $g['items'] ?? [])->values()->all(),
            'navigationGroups' => $groups,
            'activeNav' => $activeNav,
            'speciesKey' => $species->key(),
            'speciesProfile' => $species->profile(),
        ], $data);
    }
}
