<?php

namespace App\Http\Controllers\Concerns;

trait FinanceSectionViews
{
    protected function financeSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('finance', [
            'activeFinanceSection' => $activeSection,
            'financeSections' => config('finance.finance_sections'),
        ]), $data);
    }

    protected function financeFilters(\Illuminate\Http\Request $request): array
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        return [
            'filterFrom' => $from,
            'filterTo' => $to,
            'filterFarmId' => $request->input('farm_id'),
            'filterLivestockId' => $request->input('livestock_id'),
        ];
    }
}
