<?php

namespace Remls\HijriDate\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Fakes the external G2H map with deterministic data:
 * 1444-01-01 to 1446-12-01, anchored at 1444-01-01 = 2022-07-30.
 * Odd Hijri months have 30 days, even months have 29.
 */
trait FakesConversionMap
{
    protected function fakeConversionMap(): void
    {
        Http::fake(['*' => Http::response($this->conversionMapCsv())]);
    }

    protected function conversionMapCsv(): string
    {
        $rows = ["hijri_y,hijri_m,gregorian_y,gregorian_m,gregorian_d"];
        $gregorian = Carbon::parse('2022-07-30', '+5:00');
        for ($year = 1444; $year <= 1446; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $rows[] = "{$year},{$month},{$gregorian->year},{$gregorian->month},{$gregorian->day}";
                $gregorian = $gregorian->copy()->addDays($month % 2 === 1 ? 30 : 29);
            }
        }
        return implode("\n", $rows) . "\n";
    }
}
