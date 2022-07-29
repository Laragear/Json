<?php

namespace Laragear\Json\Casts;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Laragear\Json\Json;
use function app;

class AsEncryptedJson extends AsJson
{
    /**
     * Transform the attribute from the underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  string|null  $value
     * @param  array  $attributes
     * @return \Laragear\Json\Json|null
     */
    public function get($model, string $key, $value, array $attributes): ?Json
    {
        if (! isset($attributes[$key])) {
            return null;
        }

        return parent::get($model, $key, $this->encrypter($model)->decrypt($value, false), $attributes);
    }

    /**
     * Transform the attribute to its underlying model values.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array|null
     */
    public function set($model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        $array = parent::set($model, $key, $value, $attributes);

        $array[$key] = $this->encrypter($model)->encrypt($array[$key], false);

        return $array;
    }

    /**
     * Returns the encrypter used by the model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Contracts\Encryption\Encrypter
     */
    protected function encrypter(Model $model): Encrypter
    {
        return isset($model::$encrypter)
            ? $model::$encrypter
            : app('encrypter');
    }
}
