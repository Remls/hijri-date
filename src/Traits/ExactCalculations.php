<?php

namespace Remls\HijriDate\Traits;

use Remls\HijriDate\HijriDate;

trait ExactCalculations
{
    /**
     * Add specified amount of days. Uses Gregorian dates for calculation.
     * 
     * @param int $daysToAdd
     * @return \Remls\HijriDate\HijriDate
     */
    public function addDaysExact(int $daysToAdd = 1): HijriDate
    {
        if ($daysToAdd < 0)
            return $this->subDaysExact(abs($daysToAdd));

        $gregorian = $this->getGregorianDate();
        $gregorian->addDays($daysToAdd);
        $newHijri = self::createFromGregorian($gregorian);
        $this->setYear($newHijri->getYear());
        $this->setMonth($newHijri->getMonth());
        $this->setDay($newHijri->getDay());
        $this->setGregorianDate($gregorian);
        return $this;
    }

    /**
     * Subtract specified amount of days. Uses Gregorian dates for calculation.
     *
     * @param int $daysToSubtract
     * @return \Remls\HijriDate\HijriDate
     */
    public function subDaysExact(int $daysToSubtract = 1): HijriDate
    {
        if ($daysToSubtract < 0)
            return $this->addDaysExact(abs($daysToSubtract));

        $gregorian = $this->getGregorianDate();
        $gregorian->subDays($daysToSubtract);
        $newHijri = self::createFromGregorian($gregorian);
        $this->setYear($newHijri->getYear());
        $this->setMonth($newHijri->getMonth());
        $this->setDay($newHijri->getDay());
        $this->setGregorianDate($gregorian);
        return $this;
    }

    /**
     * Get the difference in days between this and another HijriDate. Uses Gregorian dates for calculation.
     * 
     * @param \Remls\HijriDate\HijriDate $other
     * @param bool $absolute Get absolute value of the difference
     * @return int
     */
    public function diffInDaysExact(HijriDate $other, bool $absolute = true): int
    {
        $gregorianThis = $this->getGregorianDate();
        $gregorianOther = $other->getGregorianDate();
        return $gregorianThis->diffInDays($gregorianOther, $absolute);
    }
}
