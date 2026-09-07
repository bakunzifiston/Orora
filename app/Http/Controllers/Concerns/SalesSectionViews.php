<?php

namespace App\Http\Controllers\Concerns;

trait SalesSectionViews
{
    protected function salesSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('sales', [
            'activeSalesSection' => $activeSection,
            'salesSections' => app(\App\Services\Species\SpeciesProfile::class)->saleSections(),
        ]), $data);
    }
}
