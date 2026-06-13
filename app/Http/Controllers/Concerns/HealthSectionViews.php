<?php

namespace App\Http\Controllers\Concerns;

trait HealthSectionViews
{
    protected function healthSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('health', [
            'activeHealthSection' => $activeSection,
            'healthSections' => config('modules.health_sections'),
        ]), $data);
    }
}
