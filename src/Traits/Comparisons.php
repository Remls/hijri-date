<?php

namespace Remls\HijriDate\Traits;

use Remls\HijriDate\HijriDate;

trait Comparisons
{
    /**
     * Compare this with another HijriDate.
     * 
     * @param HijriDate $other
     * @return int                  -1 if this is smaller, 1 if this is larger, 0 if both are equal.
     */
    public function compareWith(HijriDate $other): int
    {
        if ($this->year > $other->year) return 1;
        if ($this->year < $other->year) return -1;

        // Years are equal ...
        if ($this->month > $other->month) return 1;
        if ($this->month < $other->month) return -1;

        // Months are equal ...
        if ($this->day > $other->day) return 1;
        if ($this->day < $other->day) return -1;

        // Every value is equal
        return 0;
    }

    /**
     * Check if this is equal to another HijriDate.
     * 
     * @param HijriDate $other
     * @return bool
     */
    public function equalTo(HijriDate $other): bool
    {
        return $this->compareWith($other) === 0;
    }

    /**
     * Check if this is greater than another HijriDate.
     * 
     * @param HijriDate $other
     * @return bool
     */
    public function greaterThan(HijriDate $other): bool
    {
        return $this->compareWith($other) === 1;
    }

    /**
     * Check if this is less than another HijriDate.
     * 
     * @param HijriDate $other
     * @return bool
     */
    public function lessThan(HijriDate $other): bool
    {
        return $this->compareWith($other) === -1;
    }

    /**
     * Check if this is greater than or equal to another HijriDate.
     * 
     * @param HijriDate $other
     * @return bool
     */
    public function greaterThanOrEqualTo(HijriDate $other): bool
    {
        $result = $this->compareWith($other);
        return $result === 1 || $result === 0;
    }

    /**
     * Check if this is less than or equal to another HijriDate.
     * 
     * @param HijriDate $other
     * @return bool
     */
    public function lessThanOrEqualTo(HijriDate $other): bool
    {
        $result = $this->compareWith($other);
        return $result === -1 || $result === 0;
    }
}