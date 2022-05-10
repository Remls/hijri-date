<?php

namespace Remls\HijriDate\Rules;

use Illuminate\Contracts\Validation\Rule;
use Remls\HijriDate\HijriDate as HijriDateClass;
use InvalidArgumentException;

class ValidHijriDate implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        try {
            HijriDateClass::parse($value);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('hijri::validation.valid_hijri_date');
    }
}
