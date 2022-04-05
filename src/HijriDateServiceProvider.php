<?php

namespace Remls\HijriDate;

use Illuminate\Support\ServiceProvider;
use Remls\HijriDate\Facades\HijriDate;

class HijriDateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('hijri_date', function($app) {
            return new HijriDate();
        });
    }

    public function boot()
    {
        //
    }
}
