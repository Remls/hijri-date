<?php

namespace Remls\HijriDate\Tests;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter;
use Remls\HijriDate\Converters\MaldivesEstimateG2HConverter;
use Remls\HijriDate\Converters\MaldivesG2HConverter;
use Remls\HijriDate\HijriDate;
use RuntimeException;

final class MapConverterTest extends TestCase
{
    use FakesConversionMap;

    public function test_gregorian_to_hijri(): void
    {
        $this->fakeConversionMap();
        $this->assertSame('1444-01-01', HijriDate::createFromGregorian('2022-07-30')->toDateString());
        $this->assertSame('1444-01-12', HijriDate::createFromGregorian('2022-08-10')->toDateString());
        $this->assertSame('1444-02-01', HijriDate::createFromGregorian('2022-08-29')->toDateString());
        $this->assertSame('1444-02-29', HijriDate::createFromGregorian('2022-09-26')->toDateString());
    }

    public function test_gregorian_to_hijri_accounts_for_timezone(): void
    {
        $this->fakeConversionMap();
        // 2022-08-10 22:30 UTC is already 2022-08-11 in MVT (+5)
        $input = Carbon::parse('2022-08-10 22:30:00', 'UTC');

        $this->assertSame('1444-01-13', HijriDate::createFromGregorian($input)->toDateString());
    }

    public function test_input_carbon_is_not_mutated(): void
    {
        $this->fakeConversionMap();
        $input = Carbon::parse('2022-08-10 15:30:00', 'UTC');
        HijriDate::createFromGregorian($input);

        $this->assertSame('2022-08-10 15:30:00', $input->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $input->timezoneName);
    }

    public function test_hijri_to_gregorian(): void
    {
        $this->fakeConversionMap();
        $this->assertSame('2022-08-29', HijriDate::parse('1444-02-01')->getGregorianDate()->format('Y-m-d'));
        $this->assertSame('2022-09-26', HijriDate::parse('1444-02-29')->getGregorianDate()->format('Y-m-d'));
    }

    public function test_get_gregorian_date_returns_original_input_when_created_from_gregorian(): void
    {
        $this->fakeConversionMap();
        $date = HijriDate::createFromGregorian(Carbon::parse('2022-08-10 15:30:00', 'UTC'));

        $this->assertSame('2022-08-10 15:30:00', $date->getGregorianDate()->format('Y-m-d H:i:s'));
    }

    public function test_date_before_map_range_throws(): void
    {
        $this->fakeConversionMap();
        $this->expectException(InvalidArgumentException::class);
        HijriDate::createFromGregorian('2020-01-01');
    }

    public function test_exact_calculations_follow_real_month_lengths(): void
    {
        $this->fakeConversionMap();
        // 1444-02 has 29 days in the fake map
        $this->assertSame('1444-03-01', HijriDate::parse('1444-02-29')->addDaysExact(1)->toDateString());
        $this->assertSame('1444-02-29', HijriDate::parse('1444-03-01')->subDaysExact(1)->toDateString());
    }

    public function test_diff_in_days_exact(): void
    {
        $this->fakeConversionMap();
        $earlier = HijriDate::parse('1444-01-05');
        $later = HijriDate::parse('1444-02-04');

        $this->assertSame(29, $earlier->diffInDaysExact($later));
        $this->assertSame(29, $later->diffInDaysExact($earlier));
        $this->assertSame(-29, $later->diffInDaysExact($earlier, false));
    }

    public function test_failed_exact_calculation_leaves_date_unchanged(): void
    {
        $this->fakeConversionMap();
        $date = HijriDate::parse('1444-01-05');

        try {
            $date->subDaysExact(60);
            $this->fail('Expected exception was not thrown');
        } catch (InvalidArgumentException) {
        }

        $this->assertSame('1444-01-05', $date->toDateString());
        $this->assertSame('2022-08-03', $date->getGregorianDate()->format('Y-m-d'));
    }

    public function test_map_is_fetched_once_and_cached(): void
    {
        $this->fakeConversionMap();
        HijriDate::createFromGregorian('2022-07-30');
        HijriDate::createFromGregorian('2022-08-10');
        HijriDate::parse('1444-02-01')->getGregorianDate();

        Http::assertSentCount(1);
    }

    public function test_refresh_always_refetches(): void
    {
        $this->fakeConversionMap();
        $converter = new MaldivesG2HConverter();
        $converter->refresh();
        $converter->refresh();

        Http::assertSentCount(2);
    }

    public function test_stale_fallback_is_served_when_fetch_fails(): void
    {
        $failRemote = false;
        Http::fake(function () use (&$failRemote) {
            return $failRemote
                ? Http::response('server error', 500)
                : Http::response($this->conversionMapCsv());
        });

        $converter = new MaldivesG2HConverter();
        $goodData = $converter->getData();

        cache()->forget(config('hijri.conversion.cache_key'));
        $failRemote = true;

        $this->assertSame($goodData, $converter->getData());
        Http::assertSentCount(3);   // 1 successful fetch, then 1 failed fetch with 1 retry
    }

    public function test_fetch_fails_loudly_without_fallback(): void
    {
        Http::fake(['*' => Http::response('server error', 500)]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        (new MaldivesG2HConverter())->getData();
    }

    public function test_malformed_csv_is_rejected(): void
    {
        Http::fake(['*' => Http::response("<html>404</html>")]);

        $this->expectException(RuntimeException::class);
        (new MaldivesG2HConverter())->fetchDataFromSource();
    }

    public function test_csv_with_bad_rows_is_rejected(): void
    {
        Http::fake(['*' => Http::response("hijri_y,hijri_m,gregorian_y,gregorian_m,gregorian_d\n1444,1,2022,7,30\n1444,x,2022,8,29\n")]);

        $this->expectException(RuntimeException::class);
        (new MaldivesG2HConverter())->fetchDataFromSource();
    }

    public function test_fetch_command_warms_the_cache(): void
    {
        $this->fakeConversionMap();
        $this->artisan('hijri:fetch')->assertSuccessful();

        $this->assertTrue(cache()->has(config('hijri.conversion.cache_key')));
    }

    public function test_converter_is_resolved_as_singleton(): void
    {
        $this->assertSame(
            app(GregorianToHijriConverter::class),
            app(GregorianToHijriConverter::class)
        );
        $this->assertInstanceOf(MaldivesG2HConverter::class, app(GregorianToHijriConverter::class));
    }

    public function test_container_rebinding_overrides_config(): void
    {
        $this->app->singleton(GregorianToHijriConverter::class, fn () => new MaldivesEstimateG2HConverter());

        $this->assertInstanceOf(MaldivesEstimateG2HConverter::class, app(GregorianToHijriConverter::class));
    }

    public function test_today_hijri_helper(): void
    {
        $this->fakeConversionMap();
        Carbon::setTestNow('2022-08-29 10:00:00 +5:00');

        $this->assertSame('1444-02-01', today_hijri()->toDateString());

        Carbon::setTestNow();
    }
}
