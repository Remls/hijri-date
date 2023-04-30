<?php

namespace Remls\HijriDate\Converters;

use Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter;
use Remls\HijriDate\HijriDate;
use Carbon\Carbon;
use InvalidArgumentException;
use RuntimeException;

class MaldivesG2HConverter implements GregorianToHijriConverter
{
    private string $dataUrl;
    private string $cacheKey;
    private int $cachePeriod;

    public function __construct()
    {
        $this->dataUrl = config('hijri.conversion.data_url');
        if (empty($this->dataUrl)) {
            throw new InvalidArgumentException('Cannot load G2H map: No data URL specified in config/hijri.php');
        }
        $this->cacheKey = config('hijri.conversion.cache_key', 'hijri_to_gregorian_map');
        $this->cachePeriod = config('hijri.conversion.cache_period', 60 * 24);
    }

    public function getData(): array
    {
        return cache()->remember($this->cacheKey, $this->cachePeriod, function () {
            return $this->fetchDataFromSource();
        });
    }

    public function fetchDataFromSource()
    {
        // Load data from source
        $file = file_get_contents($this->dataUrl);
        if ($file === false) {
            throw new RuntimeException("Cannot load G2H map: Request to $this->dataUrl failed");
        }

        $csv = array_map("str_getcsv", explode("\n", $file));
        $headers = array_shift($csv);
        $data = [];
        foreach ($csv as $row) {
            $dataRow = [];
            foreach ($headers as $i => $header) {
                $dataRow[$header] = $row[$i];
            }
            $data[] = $dataRow;
        }

        // Convert to array format needed
        $padZeroFn = fn ($v) => str_pad($v, 2, '0', STR_PAD_LEFT);
        $result = [];
        foreach ($data as $row) {
            $h = implode('-', [
                $row['hijri_y'],
                $padZeroFn($row['hijri_m']),
                "01"
            ]);
            $g = implode('-', [
                $row['gregorian_y'],
                $padZeroFn($row['gregorian_m']),
                $padZeroFn($row['gregorian_d'])
            ]);
            $result[$h] = $g;
        }
        ksort($result);
        return $result;
    }

    /**
     * Get the HijriDate object from a Gregorian date.
     * 
     * @param \Carbon\Carbon $gregorian
     * @return \Remls\HijriDate\HijriDate
     */
    public function getHijriFromGregorian(Carbon $gregorian): HijriDate
    {
        $gregorian->setTimezone('+5:00');   // Ensure it is in MVT
        $gregorian->startOfDay();           // Ensure it is at midnight (so time does not affect diffInDays())
        $data = $this->getData();

        // Find the closest date in the map (the array is already sorted in ascending order)
        $closestDate = null;
        $closestDateDiff = null;
        foreach ($data as $hijriDate => $gregorianDate) {
            $diff = Carbon::parse($gregorianDate, '+5:00')->diffInDays($gregorian, false);
            if ($diff < 0) {
                // Subtracting does not work because YYYY-MM-01 minus 1 day
                // always results in YYYY-MM-30, which is sometimes wrong.
                // Use the previous date instead.
                break;
            }
            if (is_null($closestDateDiff) || $diff < $closestDateDiff) {
                $closestDate = $hijriDate;
                $closestDateDiff = $diff;
            }
        }
        // Date is too old to be found in the map
        if (is_null($closestDate)) {
            $dateDisplay = $gregorian->format('d M Y');
            throw new InvalidArgumentException("Date is too old to be converted ($dateDisplay).");
            // To resolve, do one of the following:
            // - use MaldivesEstimateG2HConverter after handling this exception
            // - use MaldivesEstimateG2HConverter in config('hijri.conversion.converter') to handle all dates with that class
            // - provide your own date map in config('hijri.conversion.data_url') with data for older dates
            // - use your own converter class in config('hijri.conversion.converter') that handles older dates
        }

        $closestDate = HijriDate::parse($closestDate);
        $closestDate->addDays($closestDateDiff, false);
        return $closestDate;
    }

    /**
     * Get the Gregorian date from a HijriDate object.
     * 
     * @param \Remls\HijriDate\HijriDate $hijri
     * @return \Carbon\Carbon
     */
    public function getGregorianFromHijri(HijriDate $hijri): Carbon
    {
        $data = $this->getData();

        // Find the closest date in the map (the array is already sorted in ascending order)
        $closestDate = null;
        $closestDateDiff = null;
        foreach ($data as $hijriDate => $gregorianDate) {
            $diff = HijriDate::parse($hijriDate)->diffInDays($hijri, false, false);
            if ($diff < 0) {
                // Kept for consistency with getHijriFromGregorian()
                break;
            }
            if (is_null($closestDateDiff) || $diff < $closestDateDiff) {
                $closestDate = $gregorianDate;
                $closestDateDiff = $diff;
            }
        }
        // Date is too old to be found in the map
        if (is_null($closestDate)) {
            $dateDisplay = $hijri->format('d M Y');
            throw new InvalidArgumentException("Hijri date is too old to be converted ($dateDisplay).");
            // To resolve, do one of the following:
            // - use MaldivesEstimateG2HConverter after handling this exception
            // - use MaldivesEstimateG2HConverter in config('hijri.conversion.converter') to handle all dates with that class
            // - provide your own date map in config('hijri.conversion.data_url') with data for older dates
            // - use your own converter class in config('hijri.conversion.converter') that handles older dates
        }

        $closestDate = Carbon::parse($closestDate, '+5:00');
        $closestDate->addDays($closestDateDiff);
        return $closestDate;
    }
}
