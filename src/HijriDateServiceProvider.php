<?php

namespace Remls\HijriDate;

use Illuminate\Support\ServiceProvider;

class HijriDateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('HijriDate', \Remls\HijriDate\HijriDate::class);
        
        $this->mergeConfigFrom(__DIR__ . '/../config/hijri.php', 'hijri');
    }

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'hijri');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/hijri.php' => config_path('hijri.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../lang' => $this->app->langPath('vendor/hijri'),
            ], 'lang');

            $this->commands([
                Console\FetchG2HMap::class,
            ]);
        }
    }
}
