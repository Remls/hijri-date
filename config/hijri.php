<?php

return [
    /**
     * List of supported locales.
     */
    'supported_locales' => ['ar', 'bn', 'dv', 'en'],

    /**
     * Locale used for formatting by default (when locale is not explicitly set).
     */
    'default_locale' => 'dv',

    /**
     * Maximum and minimum limit for Hijri date years.
     * These values are inclusive (eg: '1000-01-01' is a valid date when 'year_min' is set to 1000).
     */
    'year_max' => 1999,
    'year_min' => 1000,

    /**
     * Configuration for converting Gregorian dates to Hijri dates.
     */
    'conversion' => [
        'data_url' => 'https://gist.githubusercontent.com/Remls/b0ebba53bb2a8670f333f8a88de4aae3/raw',
        'cache_key' => 'hijri_to_gregorian_map',
        'cache_period' => 60 * 24,
    ]
];