<?php

namespace App\Http\Controllers\Concerns;

trait MilkSectionViews
{
    protected function milkSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('milk', [
            'activeMilkSection' => $activeSection,
            'milkSections' => config('modules.milk_sections'),
        ]), $data);
    }
}
