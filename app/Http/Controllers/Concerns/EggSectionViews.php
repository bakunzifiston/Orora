<?php

namespace App\Http\Controllers\Concerns;

trait EggSectionViews
{
    protected function eggSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('eggs', [
            'activeEggSection' => $activeSection,
            'eggSections' => config('modules.egg_sections'),
        ]), $data);
    }
}
