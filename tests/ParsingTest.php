<?php

namespace Remls\HijriDate\Tests;

use InvalidArgumentException;
use Remls\HijriDate\HijriDate;

final class ParsingTest extends TestCase
{
    public function test_is_parsable_accepts_valid_strings(): void
    {
        $this->assertTrue(HijriDate::isParsable('1444-01-01'));
        $this->assertTrue(HijriDate::isParsable('1444-12-30'));
    }

    public function test_is_parsable_rejects_years_outside_configured_range(): void
    {
        $this->assertFalse(HijriDate::isParsable('1-01-01'));
        $this->assertFalse(HijriDate::isParsable('999-01-01'));
        $this->assertFalse(HijriDate::isParsable('2044-01-01'));
    }

    public function test_is_parsable_rejects_invalid_values(): void
    {
        $this->assertFalse(HijriDate::isParsable('1444-13-01'));
        $this->assertFalse(HijriDate::isParsable('1444-00-01'));
        $this->assertFalse(HijriDate::isParsable('1444-01-31'));
        $this->assertFalse(HijriDate::isParsable('1444-01-00'));
        $this->assertFalse(HijriDate::isParsable('1444-1-1'));
        $this->assertFalse(HijriDate::isParsable('not a date'));
        $this->assertFalse(HijriDate::isParsable(null));
        $this->assertFalse(HijriDate::isParsable(14440101));
        $this->assertFalse(HijriDate::isParsable(['1444-01-01']));
    }

    public function test_parse_returns_correct_date(): void
    {
        $date = HijriDate::parse('1444-09-05');
        $this->assertSame(1444, $date->getYear());
        $this->assertSame(9, $date->getMonth());
        $this->assertSame(5, $date->getDay());
    }

    public function test_parse_rejects_unparsable_string(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HijriDate::parse('1444/01/01');
    }

    public function test_parse_rejects_year_outside_configured_range(): void
    {
        $this->expectException(InvalidArgumentException::class);
        HijriDate::parse('2044-01-01');
    }

    public function test_constructor_validates_year(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HijriDate(2044, 1, 1);
    }

    public function test_constructor_validates_month(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HijriDate(1444, 13, 1);
    }

    public function test_constructor_validates_day(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HijriDate(1444, 1, 31);
    }

    public function test_constructor_defaults(): void
    {
        $date = new HijriDate();
        $this->assertSame('1000-01-01', $date->toDateString());
        $this->assertSame('dv', $date->getLocale());
    }

    public function test_set_locale_rejects_unsupported_locale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new HijriDate(1444, 1, 1))->setLocale('xx');
    }

    public function test_all_configured_locales_are_accepted(): void
    {
        foreach (config('hijri.supported_locales') as $locale) {
            $date = new HijriDate(1444, 1, 1, $locale);
            $this->assertSame($locale, $date->getLocale());
        }
    }
}
