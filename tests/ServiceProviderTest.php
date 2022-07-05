<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laragear\Json\JsonServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_registers_request_macro(): void
    {
        static::assertTrue(Request::hasMacro('getJson'));
    }

    public function test_publishes_stub(): void
    {
        static::assertSame(
            [JsonServiceProvider::STUBS => $this->app->basePath('.stubs/json.php')],
            ServiceProvider::pathsToPublish(JsonServiceProvider::class, 'phpstorm')
        );
    }
}
