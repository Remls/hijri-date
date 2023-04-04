<?php

namespace Remls\HijriDate\Contracts;

use Remls\HijriDate\HijriDate;

interface GregorianToHijriConverter
{
    /**
     * Create a HijriDate object from a Gregorian date.
     * 
     * @param Carbon\Carbon $gregorian
     * @return HijriDate
     */
    public function createFromGregorian($gregorian): HijriDate;
}
