<?php

return [

    'supported' => [
        'en' => [
            'label' => 'English',
            'native' => 'English',
            'short' => 'EN',
        ],
        'rw' => [
            'label' => 'Kinyarwanda',
            'native' => 'Ikinyarwanda',
            'short' => 'RW',
        ],
    ],

    'default' => env('APP_LOCALE', 'en'),

];
