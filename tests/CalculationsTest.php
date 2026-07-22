<?php

namespace Remls\HijriDate\Tests;

use InvalidArgumentException;
use Remls\HijriDate\HijriDate;

/**
 * Tests for the approximate (non-Gregorian) calculation path,
 * which assumes 30-day months and 360-day years.
 */
final class CalculationsTest extends TestCase
{
    public function test_add_days(): void
    {
        $this->assertSame('1444-01-16', HijriDate::parse('1444-01-01')->addDays(15, false)->toDateString());
        $this->assertSame('1444-02-16', HijriDate::parse('1444-01-01')->addDays(45, false)->toDateString());
    }

    public function test_add_days_rolls_over_month_and_year(): void
    {
        $this->assertSame('1444-02-01', HijriDate::parse('1444-01-30')->addDays(1, false)->toDateString());
        $this->assertSame('1445-01-05', HijriDate::parse('1444-12-25')->addDays(10, false)->toDateString());
    }

    public function test_sub_days(): void
    {
        $this->assertSame('1444-02-01', HijriDate::parse('1444-02-05')->subDays(4, false)->toDateString());
    }

    public function test_sub_days_borrows_from_month_and_year(): void
    {
        $this->assertSame('1444-01-25', HijriDate::parse('1444-02-05')->subDays(10, false)->toDateString());
        $this->assertSame('1443-12-30', HijriDate::parse('1444-01-01')->subDays(1, false)->toDateString());
    }

    public function test_negative_amounts_delegate_to_opposite_operation(): void
    {
        $this->assertSame('1444-01-25', HijriDate::parse('1444-02-05')->addDays(-10, false)->toDateString());
        $this->assertSame('1444-02-16', HijriDate::parse('1444-01-01')->subDays(-45, false)->toDateString());
    }

    public function test_diff_in_days(): void
    {
        $earlier = HijriDate::parse('1444-01-01');
        $later = HijriDate::parse('1444-02-16');

        $this->assertSame(45, $earlier->diffInDays($later, true, false));
        $this->assertSame(45, $later->diffInDays($earlier, true, false));
        $this->assertSame(45, $earlier->diffInDays($later, false, false));
        $this->assertSame(-45, $later->diffInDays($earlier, false, false));
        $this->assertSame(0, $earlier->diffInDays(HijriDate::parse('1444-01-01'), false, false));
    }

    public function test_diff_in_days_across_years(): void
    {
        $earlier = HijriDate::parse('1444-01-01');
        $later = HijriDate::parse('1445-01-01');

        $this->assertSame(360, $earlier->diffInDays($later, true, false));
    }

    public function test_add_days_beyond_year_max_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HijriDate::parse('1999-12-30')->addDays(360, false);
    }

    public function test_sub_days_beyond_year_min_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HijriDate::parse('1000-01-01')->subDays(360, false);
    }
}
