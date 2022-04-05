<?php

namespace Remls\HijriDate\Rules;

use Illuminate\Contracts\Validation\Rule;
use Remls\HijriDate\HijriDate as HijriDateClass;

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
        return HijriDateClass::isParsable($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute must be a valid Hijri date.';
    }
}
