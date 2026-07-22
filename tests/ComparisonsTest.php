<?php

namespace Remls\HijriDate\Tests;

use Remls\HijriDate\HijriDate;

final class ComparisonsTest extends TestCase
{
    public function test_compare_with(): void
    {
        $earlier = HijriDate::parse('1444-01-15');
        $later = HijriDate::parse('1444-02-01');

        $this->assertSame(-1, $earlier->compareWith($later));
        $this->assertSame(1, $later->compareWith($earlier));
        $this->assertSame(0, $earlier->compareWith(HijriDate::parse('1444-01-15')));
    }

    public function test_compare_with_orders_by_year_then_month_then_day(): void
    {
        $date = HijriDate::parse('1444-06-15');

        $this->assertSame(-1, $date->compareWith(HijriDate::parse('1445-01-01')));
        $this->assertSame(-1, $date->compareWith(HijriDate::parse('1444-07-01')));
        $this->assertSame(-1, $date->compareWith(HijriDate::parse('1444-06-16')));
        $this->assertSame(1, $date->compareWith(HijriDate::parse('1443-12-30')));
        $this->assertSame(1, $date->compareWith(HijriDate::parse('1444-05-30')));
        $this->assertSame(1, $date->compareWith(HijriDate::parse('1444-06-14')));
    }

    public function test_comparison_helpers(): void
    {
        $earlier = HijriDate::parse('1444-01-15');
        $later = HijriDate::parse('1444-02-01');
        $sameAsEarlier = HijriDate::parse('1444-01-15');

        $this->assertTrue($earlier->equalTo($sameAsEarlier));
        $this->assertFalse($earlier->equalTo($later));

        $this->assertTrue($later->greaterThan($earlier));
        $this->assertFalse($earlier->greaterThan($later));

        $this->assertTrue($earlier->lessThan($later));
        $this->assertFalse($later->lessThan($earlier));

        $this->assertTrue($later->greaterThanOrEqualTo($earlier));
        $this->assertTrue($earlier->greaterThanOrEqualTo($sameAsEarlier));
        $this->assertFalse($earlier->greaterThanOrEqualTo($later));

        $this->assertTrue($earlier->lessThanOrEqualTo($later));
        $this->assertTrue($earlier->lessThanOrEqualTo($sameAsEarlier));
        $this->assertFalse($later->lessThanOrEqualTo($earlier));
    }
}
