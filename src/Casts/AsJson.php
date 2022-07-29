<?php

namespace Laragear\Json\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Laragear\Json\Json;

class AsJson implements CastsAttributes
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return \Laragear\Json\Json|null
     */
    public function get($model, string $key, $value, array $attributes): ?Json
    {
        return $value === null ? null : Json::fromJson($value);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array<string, Laragear\Json\Json|null>
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        return [$key => $value === null ? null : Json::wrap($value)];
    }
}
