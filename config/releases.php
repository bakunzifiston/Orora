<?php

/**
 * Platform release notes ("What's New") published by platform administrators.
 */
return [

    'categories' => [
        'new_feature' => 'New feature',
        'improvement' => 'Improvement',
        'bug_fix' => 'Bug fix',
        'important_update' => 'Important update',
        'maintenance' => 'Scheduled maintenance',
    ],

    /** Badge tone per category (maps to health-table__pill--* styles). */
    'category_tones' => [
        'new_feature' => 'ok',
        'improvement' => 'ok',
        'bug_fix' => 'muted',
        'important_update' => 'warn',
        'maintenance' => 'bad',
    ],

    /** Dashboard icon per category (layouts.partials.dashboard-nav-icon). */
    'category_icons' => [
        'new_feature' => 'box',
        'improvement' => 'chart',
        'bug_fix' => 'gear',
        'important_update' => 'shield',
        'maintenance' => 'gear',
    ],

    'audiences' => [
        'all' => 'All tenants',
        'selected' => 'Selected tenants',
    ],

    'statuses' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    /** Number of notifications shown in the header bell dropdown. */
    'bell_limit' => 8,
];
