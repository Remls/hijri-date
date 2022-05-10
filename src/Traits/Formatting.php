<?php

namespace Remls\HijriDate\Traits;

trait Formatting
{
    /**
     * Return translation in selected locale.
     * 
     * @param string $key
     * @return string
     */
    public function translate(string $key): string
    {
        return trans('hijri::'.$key, [], $this->locale);
    }

    /**
     * Returns the date with full month name in selected locale.
     * 
     * @return string
     */
    public function toFullDate(): string
    {
        $monthName = $this->translate('formatting.months.'.$this->month);
        return "$this->day $monthName $this->year";
    }

    /**
     * Returns the date in Y-m-d format.
     * 
     * @return string
     */
    public function toDateString(): string
    {
        $dateParts = [
            $this->year,
            str_pad($this->month, 2, "0", STR_PAD_LEFT),
            str_pad($this->day, 2, "0", STR_PAD_LEFT),
        ];
        return implode("-", $dateParts);
    }

    public function __toString(): string
    {
        return $this->toDateString();
    }

    public function __debugInfo(): array
    {
        $props = [
            'date' => $this->toDateString(),
        ];
        if ($this->isEstimate()) {
            $props['estimatedFrom'] = $this->estimatedFrom;
        }
        return $props;
    }
}