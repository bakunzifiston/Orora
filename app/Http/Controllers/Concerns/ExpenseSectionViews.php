<?php

namespace App\Http\Controllers\Concerns;

trait ExpenseSectionViews
{
    protected function expenseSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('expenses', [
            'activeExpenseSection' => $activeSection,
            'expenseSections' => config('modules.expense_sections'),
        ]), $data);
    }
}
