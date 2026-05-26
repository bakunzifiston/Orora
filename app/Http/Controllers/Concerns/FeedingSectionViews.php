<?php

namespace App\Http\Controllers\Concerns;

trait FeedingSectionViews
{
    protected function feedingSectionData(string $activeSection, array $data = []): array
    {
        return array_merge($this->moduleViewData('feeding', [
            'activeFeedingSection' => $activeSection,
            'feedingSections' => config('modules.feeding_sections'),
        ]), $data);
    }
}
