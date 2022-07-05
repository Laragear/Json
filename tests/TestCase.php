<?php

namespace Tests;

use Laragear\Json\JsonServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [JsonServiceProvider::class];
    }
}
