<?php

namespace App\Http\Controllers\Concerns;

trait SalesSectionViews
{
    protected function salesSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('sales', [
            'activeSalesSection' => $activeSection,
            'salesSections' => config('modules.sale_sections'),
        ]), $data);
    }
}
