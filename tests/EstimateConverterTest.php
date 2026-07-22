<?php

namespace Remls\HijriDate\Tests;

use Carbon\Carbon;
use Remls\HijriDate\Converters\MaldivesEstimateG2HConverter;
use Remls\HijriDate\HijriDate;

final class EstimateConverterTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('hijri.conversion.converter', MaldivesEstimateG2HConverter::class);
    }

    public function test_gregorian_to_hijri_estimate(): void
    {
        // 1445-01-07 was on or around 2023-07-25; allow a small margin for the estimate
        $date = HijriDate::createFromGregorian('2023-07-25');

        $this->assertSame(1445, $date->getYear());
        $this->assertSame(1, $date->getMonth());
        $this->assertGreaterThanOrEqual(5, $date->getDay());
        $this->assertLessThanOrEqual(9, $date->getDay());
    }

    public function test_hijri_to_gregorian_estimate(): void
    {
        $gregorian = (new MaldivesEstimateG2HConverter())->getGregorianFromHijri(HijriDate::parse('1445-01-15'));

        // 1445-01-15 was on or around 2023-08-02; allow a small margin for the estimate
        $this->assertTrue(
            $gregorian->between('2023-07-30', '2023-08-05'),
            "Estimated date {$gregorian->format('Y-m-d')} outside expected range"
        );
        $this->assertSame('00:00:00', $gregorian->format('H:i:s'));
    }

    public function test_input_carbon_is_not_mutated(): void
    {
        $input = Carbon::parse('2023-07-25 15:30:00', 'UTC');
        HijriDate::createFromGregorian($input);

        $this->assertSame('2023-07-25 15:30:00', $input->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $input->timezoneName);
    }
}
