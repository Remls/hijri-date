<?php

namespace Remls\HijriDate\Converters\Contracts;

use Remls\HijriDate\HijriDate;
use Carbon\Carbon;

interface GregorianToHijriConverter
{
    /**
     * Get the HijriDate object from a Gregorian date.
     * 
     * @param \Carbon\Carbon $gregorian
     * @return \Remls\HijriDate\HijriDate
     */
    public function getHijriFromGregorian(Carbon $gregorian): HijriDate;

    /**
     * Get the Gregorian date from a HijriDate object.
     * 
     * @param \Remls\HijriDate\HijriDate $hijri
     * @return \Carbon\Carbon
     */
    public function getGregorianFromHijri(HijriDate $hijri): Carbon;
}
