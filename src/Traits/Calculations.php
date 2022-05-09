<?php

namespace Remls\HijriDate\Traits;

use Remls\HijriDate\HijriDate;
use OutOfRangeException;

/**
 * All calculation methods assume all months have 30 days.
 * This isn't true in practice, of course.
 */
trait Calculations
{
    /**
     * Add specified amount of days.
     * 
     * @param int $daysToAdd
     * @return HijriDate
     */
    public function addDays(int $daysToAdd = 1): HijriDate
    {
        if ($daysToAdd < 0)
            return $this->subDays(abs($daysToAdd));

        // Work with copies
        $hYear = $this->year;
        $hMonth = $this->month;
        $hDay = $this->day;

        $yearsToAdd = intdiv($daysToAdd, self::DAYS_PER_YEAR);
        $daysToAdd -= $yearsToAdd * self::DAYS_PER_YEAR;
        $monthsToAdd = intdiv($daysToAdd, self::DAYS_PER_MONTH);
        $daysToAdd -= $monthsToAdd * self::DAYS_PER_MONTH;

        $hDay += $daysToAdd;
        if ($hDay > self::DAY_MAX) {
            $hDay -= self::DAY_MAX;
            $hMonth++;
        }
        $hMonth += $monthsToAdd;
        if ($hMonth > self::MONTH_MAX) {
            $hMonth -= self::MONTH_MAX;
            $hYear++;
        }
        $hYear += $yearsToAdd;
        if ($hYear > config('hijri.year_max', self::FALLBACK_YEAR_MAX))
            throw new OutOfRangeException("Date value out of acceptable range.");

        $this->year = $hYear;
        $this->month = $hMonth;
        $this->day = $hDay;
        $this->resetEstimation();
        return $this;
    }

    /**
     * Subtract specified amount of days.
     *
     * @param int $daysToSubtract
     * @return HijriDate
     */
    public function subDays(int $daysToSubtract = 1): HijriDate
    {
        if ($daysToSubtract < 0)
            return $this->addDays(abs($daysToSubtract));

        // Work with copies
        $hYear = $this->year;
        $hMonth = $this->month;
        $hDay = $this->day;

        $yearsToSubtract = intdiv($daysToSubtract, self::DAYS_PER_YEAR);
        $daysToSubtract -= $yearsToSubtract * self::DAYS_PER_YEAR;
        $monthsToSubtract = intdiv($daysToSubtract, self::DAYS_PER_MONTH);
        $daysToSubtract -= $monthsToSubtract * self::DAYS_PER_MONTH;

        $hDay -= $daysToSubtract;
        if ($hDay < self::DAY_MIN) {
            $hDay += self::DAY_MAX;
            $hMonth--;
        }
        $hMonth -= $monthsToSubtract;
        if ($hMonth < self::MONTH_MIN) {
            $hMonth += self::MONTH_MAX;
            $hYear--;
        }
        $hYear -= $yearsToSubtract;
        if ($hYear < config('hijri.year_min', self::FALLBACK_YEAR_MIN))
            throw new OutOfRangeException("Date value out of acceptable range.");

        $this->year = $hYear;
        $this->month = $hMonth;
        $this->day = $hDay;
        $this->resetEstimation();
        return $this;
    }
}