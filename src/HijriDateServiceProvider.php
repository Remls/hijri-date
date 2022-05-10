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
        $this->mergeConfigFrom(__DIR__.'/../config/hijri.php', 'hijri');
    }

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'hijri');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/hijri.php' => config_path('hijri.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/hijri'),
            ], 'lang');
        }
    }
}
