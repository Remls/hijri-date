<?php

namespace Remls\HijriDate\Converters;

use Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter;
use Remls\HijriDate\HijriDate;
use IntlDateFormatter;
use IntlCalendar;

class MaldivesEstimateG2HConverter implements GregorianToHijriConverter
{
    /**
     * Create a HijriDate object from a Gregorian date.
     * 
     * @param Carbon\Carbon $gregorian
     * @return HijriDate
     */
    public function createFromGregorian($gregorian): HijriDate
    {
        $gregorian->setTimezone('+5:00');   // Ensure it is in MVT
        $formatter = IntlDateFormatter::create(
            'en_US',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Indian/Maldives',
            IntlCalendar::createInstance('Indian/Maldives', "en_US@calendar=islamic"),
            'yyyy-MM-dd'
        );
        return HijriDate::parse($formatter->format($gregorian));
    }
}