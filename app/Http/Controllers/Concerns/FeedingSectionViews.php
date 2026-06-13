<?php

namespace App\Http\Controllers\Concerns;

trait FeedingSectionViews
{
    protected function feedingSectionData(string $activeSection, array $data = []): array
    {
        $activeNav = $activeSection === 'calculator' ? 'feed-calculator' : 'feeding';

        return array_merge($this->moduleViewData($activeNav, [
            'activeFeedingSection' => $activeSection,
            'feedingSections' => config('modules.feeding_sections'),
        ]), $data);
    }
}
