<?php

namespace App\Http\Controllers\Concerns;

trait CustomerSectionViews
{
    protected function customerSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('customers', [
            'activeCustomerSection' => $activeSection,
            'customerSections' => config('modules.customer_sections'),
        ]), $data);
    }
}
