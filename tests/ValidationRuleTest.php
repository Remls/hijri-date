<?php

namespace Remls\HijriDate\Tests;

use Illuminate\Support\Facades\Validator;
use Remls\HijriDate\Rules\ValidHijriDate;

final class ValidationRuleTest extends TestCase
{
    private function validate($value): \Illuminate\Validation\Validator
    {
        return Validator::make(['date' => $value], ['date' => new ValidHijriDate]);
    }

    public function test_valid_dates_pass(): void
    {
        $this->assertTrue($this->validate('1444-01-01')->passes());
        $this->assertTrue($this->validate('1999-12-30')->passes());
    }

    public function test_invalid_strings_fail(): void
    {
        $this->assertTrue($this->validate('not a date')->fails());
        $this->assertTrue($this->validate('1444-13-01')->fails());
        $this->assertTrue($this->validate('1444-01-31')->fails());
        $this->assertTrue($this->validate('2044-01-01')->fails());
    }

    public function test_non_string_values_fail_without_crashing(): void
    {
        $this->assertTrue($this->validate(['1444-01-01'])->fails());
        $this->assertTrue($this->validate(14440101)->fails());
        $this->assertTrue($this->validate(null)->fails());
    }

    public function test_failure_message_is_translated(): void
    {
        $validator = $this->validate('not a date');

        $this->assertSame(
            'The date is not a valid Hijri date.',
            $validator->errors()->first('date')
        );
    }
}
