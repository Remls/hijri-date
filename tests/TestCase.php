<?php

namespace Remls\HijriDate\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Remls\HijriDate\HijriDateServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [HijriDateServiceProvider::class];
    }
}
