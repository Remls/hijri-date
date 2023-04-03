<?php

namespace Remls\HijriDate;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use IntlDateFormatter;
use IntlCalendar;
use InvalidArgumentException;
use Remls\HijriDate\Traits\Calculations;
use Remls\HijriDate\Traits\Comparisons;
use Remls\HijriDate\Traits\Formatting;

/**
 * @method  HijriDate   addDays(int $daysToAdd = 1)             Add specified amount of days.
 * @method  HijriDate   subDays(int $daysToSubtract = 1)        Subtract specified amount of days.
 * @method  int         compareWith(HijriDate $other)           Compare this with another HijriDate.
 * @method  bool        equalTo(HijriDate $other)               Check if this is equal to another HijriDate.
 * @method  bool        greaterThan(HijriDate $other)           Check if this is greater than another HijriDate.
 * @method  bool        lessThan(HijriDate $other)              Check if this is less than another HijriDate.
 * @method  bool        greaterThanOrEqualTo(HijriDate $other)  Check if this is greater than or equal to another HijriDate.
 * @method  bool        lessThanOrEqualTo(HijriDate $other)     Check if this is less than or equal to another HijriDate.
 * @method  string      translate(string $key)                  Returns translation in selected locale.
 * @method  string      format(string $format)                  Returns the date formatted according to given format.
 * @method  string      toFullDate()                            Returns the date in "j F Y" format. (1 Muharram 1000)
 * @method  string      toDateString()                          Returns the date in "Y-m-d" format. (1000-01-01)
 * @method  string      __toString()
 * @method  array       __debugInfo()
 */
class HijriDate implements CastsAttributes, SerializesCastableAttributes
{
    use Calculations, Comparisons, Formatting;

    const MUHARRAM      = 1;
    const SAFAR         = 2;
    const RABI_I        = 3;
    const RABI_II       = 4;
    const JUMAD_I       = 5;
    const JUMAD_II      = 6;
    const RAJAB         = 7;
    const SHABAN        = 8;
    const RAMADAN       = 9;
    const SHAWWAL       = 10;
    const DHUL_QADA     = 11;
    const DHUL_HIJJA    = 12;

    const SUNDAY    = 0;
    const MONDAY    = 1;
    const TUESDAY   = 2;
    const WEDNESDAY = 3;
    const THURSDAY  = 4;
    const FRIDAY    = 5;
    const SATURDAY  = 6;

    const DAYS_PER_WEEK = 7;
    const DAYS_PER_MONTH = 30;
    const DAYS_PER_YEAR = 360;

    // Used as fallbacks if config values are not provided.
    private const FALLBACK_YEAR_MAX = 1999;  // inclusive
    private const FALLBACK_YEAR_MIN = 1000;  // inclusive
    private const FALLBACK_DEFAULT_LOCALE = 'dv';

    private const PARSABLE_REGEX = "/^\d{1,4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|30)$/";
    private const MONTH_MAX = 12;   // inclusive
    private const MONTH_MIN = 1;    // inclusive
    private const DAY_MAX = 30;     // inclusive
    private const DAY_MIN = 1;      // inclusive

    private int $year;
    private int $month;
    private int $day;
    private string $locale;
    private ?Carbon $estimatedFrom = null;

    public function __construct(
        ?int $year = null,
        ?int $month = null,
        ?int $day = null,
        ?string $locale = null
    ) {
        // Use defaults if null
        if (! $year)    $year = config('hijri.year_min', self::FALLBACK_YEAR_MIN);
        if (! $month)   $month = self::MONTH_MIN;
        if (! $day)     $day = self::DAY_MIN;
        if (! $locale)  $locale = config('hijri.default_locale', self::FALLBACK_DEFAULT_LOCALE);

        $this->setYear($year);
        $this->setMonth($month);
        $this->setDay($day);
        $this->setLocale($locale);
        return $this;
    }

    /**
     * Checks if a given string can be transformed into a HijriDate object.
     * 
     * To transform, use HijriDate::parse()
     * 
     * @param mixed $test
     * @return bool
     */
    public static function isParsable($test): bool
    {
        if (!is_string($test)) return false;
        return preg_match(self::PARSABLE_REGEX, $test) === 1;
    }

    /**
     * Create a HijriDate object from a string.
     * 
     * @param string $hijri     Must be in the format Y-m-d
     * @return HijriDate
     */
    public static function parse(string $hijri): HijriDate
    {
        if (! self::isParsable($hijri))
            throw new InvalidArgumentException("This date cannot be parsed as a Hijri date: $hijri");

        $dateParts = explode("-", $hijri);
        $hYear = (int) $dateParts[0];
        $hMonth = (int) $dateParts[1];
        $hDay = (int) $dateParts[2];

        return new self($hYear, $hMonth, $hDay);
    }

    /**
     * Get an approximate Hijri date for a given Gregorian date.
     * 
     * @param Carbon\Carbon|string|null $gregorian      Optional. Uses current time if not passed.
     * @return HijriDate
     */
    public static function getEstimateFromGregorian($gregorian = null): HijriDate
    {
        if (is_null($gregorian)) $gregorian = now();
        if (is_string($gregorian)) $gregorian = Carbon::parse($gregorian);
        $gregorian->setTimezone('+5:00');
        $formatter = IntlDateFormatter::create(
            'en_US',
            IntlDateFormatter::FULL,
            IntlDateFormatter::FULL,
            'Indian/Maldives',
            IntlCalendar::createInstance('Indian/Maldives', "en_US@calendar=islamic"),
            'yyyy-MM-dd'
        );
        $estimate = self::parse($formatter->format($gregorian));
        $estimate->estimatedFrom = $gregorian;
        return $estimate;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): HijriDate
    {
        $max = config('hijri.year_max', self::FALLBACK_YEAR_MAX);
        $min = config('hijri.year_min', self::FALLBACK_YEAR_MIN);
        if ($year > $max || $year < $min)
            throw new InvalidArgumentException("Invalid year. Supported values: $min-$max.");
        $this->year = $year;
        return $this;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): HijriDate
    {
        $max = self::MONTH_MAX;
        $min = self::MONTH_MIN;
        if ($month > $max || $month < $min)
            throw new InvalidArgumentException("Invalid month. Supported values: $min-$max.");
        $this->month = $month;
        return $this;
    }

    public function getDay(): int
    {
        return $this->day;
    }

    public function setDay(int $day): HijriDate
    {
        $max = self::DAY_MAX;
        $min = self::DAY_MIN;
        if ($day > $max || $day < $min)
            throw new InvalidArgumentException("Invalid day. Supported values: $min-$max.");
        $this->day = $day;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): HijriDate
    {
        $supportedLocales = config('hijri.supported_locales');
        if (! in_array($locale, $supportedLocales)) {
            $localesList = implode(", ", $supportedLocales);
            throw new InvalidArgumentException("Invalid locale. Supported values: $localesList");
        }
        $this->locale = $locale;
        return $this;
    }

    public function isEstimate(): bool
    {
        return !is_null($this->estimatedFrom);
    }

    public function getEstimatedFrom(): ?Carbon
    {
        return $this->estimatedFrom;
    }

    public function resetEstimation(): HijriDate
    {
        $this->estimatedFrom = null;
        return $this;
    }

    // -- IMPLEMENTED METHODS BELOW THIS LINE --

    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return HijriDate|null
     */
    public function get($model, $key, $value, $attributes)
    {
        if (is_null($value))
            return null;
        return self::parse($value);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string|null
     */
    public function set($model, $key, $value, $attributes)
    {
        if (is_null($value))
            return null;
        if ($value instanceof self)
            return $value->toDateString();
        return self::parse($value)->toDateString();
    }

    /**
     * Get the serialized representation of the value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     */
    public function serialize($model, $key, $value, $attributes)
    {
        return $value->toDateString();
    }
}
