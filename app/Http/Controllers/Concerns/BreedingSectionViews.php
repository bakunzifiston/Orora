<?php

namespace App\Http\Controllers\Concerns;

trait BreedingSectionViews
{
    protected function breedingSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('breeding', [
            'activeBreedingSection' => $activeSection,
            'breedingSections' => config('modules.breeding_sections'),
        ]), $data);
    }
}
