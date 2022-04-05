<?php

namespace Remls\HijriDate\Facades;

use Illuminate\Support\Facades\Facade;

class HijriDate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hijri_date';
    }
}