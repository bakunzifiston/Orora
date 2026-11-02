<?php

/**
 * Help Center documents (PDFs in resources/documentation, version info in manifest.json
 * which is written by documentation/OroraFarm_Documentation_Source/build/build.mjs).
 *
 * audience: who may download the file.
 *   member    - any active workspace user
 *   workspace_admin - workspace admins (and platform admins)
 *   platform  - platform (/admin) administrators only
 */
return [

    'path' => resource_path('documentation'),

    'documents' => [
        'end-user' => [
            'title' => 'End-User Guide',
            'file' => 'OroraFarm_End_User_Guide.pdf',
            'audience' => 'member',
            'summary' => 'Step-by-step data entry guide: logging in, registering animals and flocks, recording milk, eggs, feeding, health, breeding, sales and expenses.',
        ],
        'admin' => [
            'title' => 'Administrator Guide',
            'file' => 'OroraFarm_Administrator_Guide.pdf',
            'audience' => 'workspace_admin',
            'summary' => 'For farm owners and managers: users and module permissions, farm settings, KPIs, data quality, notifications and recommended practices.',
        ],
        'technical' => [
            'title' => 'Technical Documentation',
            'file' => 'OroraFarm_Technical_Documentation.pdf',
            'audience' => 'platform',
            'summary' => 'For developers and IT: architecture, database schema and ERD, business logic, security, deployment and known limitations.',
        ],
        'complete' => [
            'title' => 'Complete Documentation',
            'file' => 'OroraFarm_Complete_Documentation.pdf',
            'audience' => 'platform',
            'summary' => 'All three manuals combined in one PDF.',
        ],
    ],
];
