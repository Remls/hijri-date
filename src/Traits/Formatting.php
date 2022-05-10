<?php

namespace Remls\HijriDate\Traits;

trait Formatting
{
    /**
     * Returns translation in selected locale.
     * 
     * @param string $key
     * @return string
     */
    public function translate(string $key): string
    {
        return trans('hijri::'.$key, [], $this->locale);
    }

    /**
     * Returns the date formatted according to given format.
     * 
     * @param string $format
     * @param bool $transformNumerals   Whether to also transform numerals, if the current locale uses a different numeral system (eg: ١٢٣ instead of 123).
     * @return string
     */
    public function format(string $format, bool $transformNumerals = false): string
    {
        // Not possible to get any weekday information without estimatedFrom.
        $dayOfWeek = $this->isEstimate()
            ? $this->estimatedFrom->dayOfWeek   // 0 for Sun ... 6 for Sat
            : null;

        $returnString = "";
        $formatChars = mb_str_split($format);
        for ($i = 0; $i < count($formatChars); $i++) { 
            switch ($formatChars[$i]) {
                /** day */
                case 'd':   // Day of month with leading zero
                    $returnString .= str_pad($this->day, 2, "0", STR_PAD_LEFT);
                    break;
                case 'D':   // Weekday (short)
                    if (!is_null($dayOfWeek)) {
                        $returnString .= $this->translate('formatting.weekdays_short.'.$dayOfWeek);
                    }
                    break;
                case 'j':   // Day of month without leading zero
                    $returnString .= $this->day;
                    break;
                case 'l':   // Weekday
                    if (!is_null($dayOfWeek)) {
                        $returnString .= $this->translate('formatting.weekdays.'.$dayOfWeek);
                    }
                    break;
                // case 'S':   // Ordinal
                //     break;
        
                /** month */
                case 'F':   // Month
                    $returnString .= $this->translate('formatting.months.'.$this->month);
                    break; 
                case 'm':   // Month (int value, with leading zero)
                    $returnString .= str_pad($this->month, 2, "0", STR_PAD_LEFT);
                    break;
                case 'M':   // Month (short)
                    $returnString .= $this->translate('formatting.months_short.'.$this->month);
                    break;
                case 'n':   // Month (int value, without leading zero)
                    $returnString .= $this->month;
                    break;

                /** year */
                case 'y':   // Year (last 2 digits)
                    $returnString .= str_pad($this->year % 100, 2, "0", STR_PAD_LEFT);
                    break;
                case 'Y':   // Year
                    $returnString .= $this->year;
                    break;

                case '\\':  // Escape next character
                    if ($i < count($formatChars) - 1) {
                        $i++;
                    }
        
                default:
                    $returnString .= $formatChars[$i];
                    break;
            }
        }
        if ($transformNumerals) {
            $returnString = self::transformNumerals($returnString, $this->locale);
        }
        return $returnString;
    }

    /**
     * Returns the date in "j F Y" format. (1 Muharram 1000)
     * 
     * @return string
     */
    public function toFullDate(): string
    {
        return $this->format("j F Y");
    }

    /**
     * Returns the date in "Y-m-d" format. (1000-01-01)
     * 
     * @return string
     */
    public function toDateString(): string
    {
        return $this->format("Y-m-d");
    }

    /**
     * Transform numerals in the input string to those used by the provided locale.
     * 
     * @param string $input
     * @param string $locale
     * @return string
     */
    private static function transformNumerals(string $input, string $locale): string
    {
        $inputChars = mb_str_split($input);
        for ($i = 0; $i < count($inputChars); $i++) { 
            if (is_numeric($inputChars[$i])) {
                $inputChars[$i] = trans('hijri::formatting.numerals.'.$inputChars[$i], [], $locale);
            }
        }
        return implode("", $inputChars);
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