<?php

return [
    'default' => env('APP_LOCALE', 'en'),

    'supported' => [
        'en' => 'English',
        'fr' => 'Français',
    ],

    // Country-based detection is deliberately opt-in. Enable this only when
    // the application is behind a trusted proxy (for example Cloudflare)
    // that overwrites the configured country header.
    'country_detection' => [
        'enabled' => (bool) env('LOCALIZATION_COUNTRY_DETECTION_ENABLED', false),
        'header' => env('LOCALIZATION_COUNTRY_HEADER', 'CF-IPCountry'),
        'country_locales' => [
            'FR' => 'fr',
            'GP' => 'fr',
            'GF' => 'fr',
            'MQ' => 'fr',
            'RE' => 'fr',
            'YT' => 'fr',
            'PM' => 'fr',
            'BL' => 'fr',
            'MF' => 'fr',
            'WF' => 'fr',
            'PF' => 'fr',
            'NC' => 'fr',
        ],
    ],
];
