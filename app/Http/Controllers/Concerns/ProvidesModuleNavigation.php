<?php

namespace App\Http\Controllers\Concerns;

use App\Services\Species\SpeciesProfile;

trait ProvidesModuleNavigation
{
    protected function moduleViewData(string $activeNav, array $data = []): array
    {
        $species = app(SpeciesProfile::class);

        return array_merge([
            'navigation' => $species->navigation(),
            'navigationGroups' => $species->navigationGroups(),
            'activeNav' => $activeNav,
            'speciesKey' => $species->key(),
            'speciesProfile' => $species->profile(),
        ], $data);
    }
}
