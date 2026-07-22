<?php

namespace Remls\HijriDate\Tests;

use Remls\HijriDate\HijriDate;

final class FormattingTest extends TestCase
{
    use FakesConversionMap;

    public function test_format_date_parts(): void
    {
        $date = HijriDate::parse('1444-09-05')->setLocale('en');

        $this->assertSame('1444-09-05', $date->format('Y-m-d'));
        $this->assertSame('5 9 44', $date->format('j n y'));
        $this->assertSame('05 09', $date->format('d m'));
        $this->assertSame('5 Ramadan 1444', $date->format('j F Y'));
        $this->assertSame('Rmd', $date->format('M'));
    }

    public function test_format_escapes_characters(): void
    {
        $date = HijriDate::parse('1444-09-05');

        $this->assertSame('Y 1444', $date->format('\\Y Y'));
    }

    public function test_to_date_string_and_full_date(): void
    {
        $date = HijriDate::parse('1444-09-01')->setLocale('en');

        $this->assertSame('1444-09-01', $date->toDateString());
        $this->assertSame('1444-09-01', (string) $date);
        $this->assertSame('1 Ramadan 1444', $date->toFullDate());
    }

    public function test_month_names_in_all_locales(): void
    {
        $expected = [
            'ar' => 'رمضان',
            'bn' => 'রমজান',
            'dv' => 'ރަމަޟާން',
            'en' => 'Ramadan',
            'id' => 'Ramadan',
            'ms' => 'Ramadan',
            'ur' => 'رمضان',
        ];

        foreach ($expected as $locale => $month) {
            $date = new HijriDate(1444, 9, 1, $locale);
            $this->assertSame($month, $date->format('F'), "Month name mismatch for locale: $locale");
        }
    }

    public function test_weekday_names_use_gregorian_date(): void
    {
        $this->fakeConversionMap();
        // 2022-07-30 is a Saturday
        $date = HijriDate::createFromGregorian('2022-07-30')->setLocale('en');

        $this->assertSame('Saturday', $date->format('l'));
        $this->assertSame('Sat', $date->format('D'));
    }

    public function test_numeral_transformation(): void
    {
        $this->assertSame('1444', (new HijriDate(1444, 9, 1, 'en'))->format('Y', true));
        $this->assertSame('١٤٤٤', (new HijriDate(1444, 9, 1, 'ar'))->format('Y', true));
        $this->assertSame('১৪৪৪', (new HijriDate(1444, 9, 1, 'bn'))->format('Y', true));
        $this->assertSame('۱۴۴۴', (new HijriDate(1444, 9, 1, 'ur'))->format('Y', true));
    }

    public function test_numerals_untouched_without_transform_flag(): void
    {
        $this->assertSame('1444', (new HijriDate(1444, 9, 1, 'ar'))->format('Y'));
    }
}
