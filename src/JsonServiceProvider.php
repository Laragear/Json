<?php

namespace Laragear\Json;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\ParameterBag;

class JsonServiceProvider extends ServiceProvider
{
    public const STUBS = __DIR__.'/../.stubs/json.php';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Request::macro('getJson', function (string|int $key = null, mixed $default = null): mixed {
            /** @var \Illuminate\Http\Request $this */

            // This will instance the JSON property of the Request to avoid duplicating the
            // JSON data, or replacing the JSON if the developer has edited it. Since the
            // Json class extends ParameterBag, there isn't any incompatibility risks.
            // @phpstan-ignore-next-line
            if (! $this->json instanceof Json) {
                // @phpstan-ignore-next-line
                $this->json = $this->json instanceof ParameterBag
                    // @phpstan-ignore-next-line
                    ? Json::make($this->json->all())
                    : Json::fromJson($this->getContent());
            }

            // @phpstan-ignore-next-line
            return $key === null ? $this->json : $this->json->get($key, $default);
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([static::STUBS => $this->app->basePath('.stubs/json.php')], 'phpstorm');
        }
    }
}
