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
];