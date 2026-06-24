@include('central.partials.filter-toolbar', [
    'toolbarTitle' => 'Platform overview',
    'toolbarSubtitle' => 'Showing data for',
    'toolbarAction' => route('central.dashboard'),
    'toolbarFormId' => 'admin-dash-filters-form',
    'toolbarPeriodId' => 'admin_filter_period',
    'toolbarDatesId' => 'admin-dash-custom-dates',
])
