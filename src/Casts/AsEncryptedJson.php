<?php

namespace Laragear\Json\Casts;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Laragear\Json\Json;
use function app;

class AsEncryptedJson extends AsJson
{
    /**
     * @inheritDoc
     */
    public function get($model, string $key, $value, array $attributes): ?Json
    {
        if (! isset($attributes[$key])) {
            return null;
        }

        return parent::get($model, $key, $this->encrypter($model)->decrypt($value, false), $attributes);
    }

    /**
     * @inheritDoc
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
     */
    protected function encrypter(Model $model): Encrypter
    {
        return $model::$encrypter ?? app('encrypter');
    }
}
