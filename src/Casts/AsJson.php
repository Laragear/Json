<?php

namespace Laragear\Json\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Laragear\Json\Json;

class AsJson implements CastsAttributes
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): ?Json
    {
        return $value === null ? null : Json::fromJson($value);
    }

    /**
     * @inheritDoc
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        return [$key => $value === null ? null : Json::wrap($value)];
    }
}
