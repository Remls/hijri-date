<?php

namespace Remls\HijriDate\Converters;

use Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter;
use Remls\HijriDate\HijriDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class MaldivesG2HConverter implements GregorianToHijriConverter
{
    private const CSV_HEADERS = ['hijri_y', 'hijri_m', 'gregorian_y', 'gregorian_m', 'gregorian_d'];

    private string $dataUrl;
    private string $cacheKey;
    private string $fallbackCacheKey;
    private int $cachePeriod;

    public function __construct()
    {
        $this->dataUrl = config('hijri.conversion.data_url');
        if (empty($this->dataUrl)) {
            throw new InvalidArgumentException('Cannot load G2H map: No data URL specified in config/hijri.php');
        }
        $this->cacheKey = config('hijri.conversion.cache_key', 'hijri_to_gregorian_map');
        $this->fallbackCacheKey = $this->cacheKey . '_fallback';
        $this->cachePeriod = config('hijri.conversion.cache_period', 60 * 60 * 6);
    }

    public function getData(): array
    {
        $cached = cache()->get($this->cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            return $this->refresh();
        } catch (Throwable $e) {
            // Serve the last successfully fetched map, if there is one
            $fallback = cache()->get($this->fallbackCacheKey);
            if ($fallback !== null) {
                cache()->put($this->cacheKey, $fallback, $this->cachePeriod);
                return $fallback;
            }
            throw $e;
        }
    }

    /**
     * Fetch fresh data from source, and store it in cache.
     *
     * @return array
     */
    public function refresh(): array
    {
        $data = $this->fetchDataFromSource();
        cache()->put($this->cacheKey, $data, $this->cachePeriod);
        cache()->forever($this->fallbackCacheKey, $data);
        return $data;
    }

    public function fetchDataFromSource(): array
    {
        $response = Http::connectTimeout(5)
            ->timeout(15)
            ->retry(2, 100)
            ->get($this->dataUrl)
            ->throw();

        $lines = preg_split('/\R/', trim($response->body()));
        $headers = str_getcsv(array_shift($lines), ',', '"', '');
        $missingHeaders = array_diff(self::CSV_HEADERS, $headers);
        if (!empty($missingHeaders)) {
            $missingList = implode(', ', $missingHeaders);
            throw new RuntimeException("Cannot load G2H map: Data is missing expected columns ($missingList)");
        }

        $padZeroFn = fn ($v) => str_pad($v, 2, '0', STR_PAD_LEFT);
        $result = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cells = str_getcsv($line, ',', '"', '');
            if (count($cells) !== count($headers)) {
                throw new RuntimeException("Cannot load G2H map: Malformed row in data: $line");
            }
            $row = array_map('trim', array_combine($headers, $cells));
            foreach (self::CSV_HEADERS as $column) {
                if (!ctype_digit($row[$column])) {
                    throw new RuntimeException("Cannot load G2H map: Malformed row in data: $line");
                }
            }

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
        if (empty($result)) {
            throw new RuntimeException("Cannot load G2H map: No data rows found in $this->dataUrl");
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
