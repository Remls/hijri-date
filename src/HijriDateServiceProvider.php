<?php

namespace Remls\HijriDate;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Remls\HijriDate\Converters\Contracts\GregorianToHijriConverter;

class HijriDateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('HijriDate', \Remls\HijriDate\HijriDate::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/hijri.php', 'hijri');

        $this->app->singleton(GregorianToHijriConverter::class, function () {
            $converter = config(
                'hijri.conversion.converter',
                \Remls\HijriDate\Converters\MaldivesG2HConverter::class
            );
            if (!class_exists($converter))
                throw new InvalidArgumentException("Invalid converter class: $converter");
            if (!in_array(GregorianToHijriConverter::class, class_implements($converter)))
                throw new InvalidArgumentException("Converter class must implement GregorianToHijriConverter: $converter");

            return new $converter();
        });

        require_once __DIR__ . '/helpers.php';
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
