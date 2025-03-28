<?php

namespace Laragear\Json;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Laragear\Json\Http\Requests\RequestJson;
use Symfony\Component\HttpFoundation\ParameterBag;
use function app;

class JsonServiceProvider extends ServiceProvider
{
    public const STUBS = __DIR__.'/../.stubs/json.php';

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        Request::macro('getJson', function (string|int|null $key = null, mixed $default = null): mixed {
            // This will instance the JSON property of the Request to avoid duplicating the
            // JSON data, or replacing the JSON if the developer has edited it. Since the
            // Json class extends ParameterBag, there isn't any incompatibility risks.
            if (!$this->json instanceof Json) { // @phpstan-ignore-line
                $this->json = $this->json instanceof ParameterBag // @phpstan-ignore-line
                    ? Json::make($this->json->all()) // @phpstan-ignore-line
                    : Json::fromJson($this->getContent());
            }

            return $key === null ? $this->json : $this->json->get($key, $default); // @phpstan-ignore-line
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([static::STUBS => $this->app->basePath('.stubs/json.php')], 'phpstorm');
        }
    }
}
