<?php

use Remls\HijriDate\HijriDate;

if (!function_exists('today_hijri')) {
    /**
     * Get today's Hijri date.
     *
     * @return \Remls\HijriDate\HijriDate
     */
    function today_hijri(): HijriDate
    {
        return HijriDate::createFromGregorian(today());
    }
}
