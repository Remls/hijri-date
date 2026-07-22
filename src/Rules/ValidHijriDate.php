<?php

namespace Remls\HijriDate\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Remls\HijriDate\HijriDate as HijriDateClass;
use InvalidArgumentException;

class ValidHijriDate implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('hijri::validation.valid_hijri_date')->translate();
            return;
        }

        try {
            HijriDateClass::parse($value);
        } catch (InvalidArgumentException $e) {
            $fail('hijri::validation.valid_hijri_date')->translate();
        }
    }
}
