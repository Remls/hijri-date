<?php

namespace Remls\HijriDate\Console;

use Illuminate\Console\Command;

class FetchG2HMap extends Command
{
    protected $signature = 'hijri:fetch';

    protected $description = 'Manually refetch the map of Hijri dates to Gregorian dates from external source';

    public function handle()
    {
        $this->info("Clearing any existing cache...");
        $cacheKey = config('hijri.conversion.cache_key', 'hijri_to_gregorian_map');
        cache()->forget($cacheKey);

        $this->info("Fetching data from source...");
        $converter = new \Remls\HijriDate\Converters\MaldivesG2HConverter();
        $converter->getData();

        $this->info("Done!");
    }
}
