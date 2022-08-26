<?php

namespace Laragear\Json;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Conditionable;
use Illuminate\Support\Traits\Tappable;
use JsonSerializable;
use Laragear\Compare\Comparable;
use Stringable;
use Symfony\Component\HttpFoundation\ParameterBag;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_shift;
use function count;
use function data_get;
use function data_set;
use function explode;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function property_exists;

class Json extends ParameterBag implements Stringable, ArrayAccess, JsonSerializable, Arrayable, Jsonable, Responsable
{
    use Conditionable;
    use Tappable;
    use Comparable;

    /**
     * The JSON "parameters".
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Create a new Json instance.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $parameters
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    final public function __construct(Arrayable|iterable $parameters = [])
    {
        if ($parameters instanceof Arrayable) {
            $parameters = $parameters->toArray();
        }

        foreach ($parameters as $key => $value) {
            $this->parameters[$key] = $value;
        }
    }

    /**
     * Returns a JSON value in "dot" notation.
     *
     * @param  array|string|int|null  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    public function get(array|string|int|null $key, mixed $default = null): mixed
    {
        return data_get($this->parameters, $key, $default);
    }

    /**
     * Returns many values of the JSON as a single array.
     *
     * @param  array  $keys
     * @param  mixed|null  $default
     * @return array
     */
    public function getMany(array $keys, mixed $default = null): array
    {
        $array = [];

        foreach ($keys as $key) {
            $array[$key] = $this->get($key, $default);
        }

        return $array;
    }

    /**
     * Sets a JSON value in "dot" notation.
     *
     * @param  array|string|int  $key
     * @param  mixed  $value
     * @param  bool  $overwrite
     * @return $this
     */
    public function set(array|string|int $key, mixed $value, bool $overwrite = true): static
    {
        data_set($this->parameters, $key, $value, $overwrite);

        return $this;
    }

    /**
     * Sets many JSON values in "dot" notation.
     *
     * @param  array  $keys
     * @param  bool  $overwrite
     * @return $this
     */
    public function setMany(array $keys, bool $overwrite = true): static
    {
        foreach ($keys as $key => $value) {
            $this->set($key, $value, $overwrite);
        }

        return $this;
    }

    /**
     * Fills a key with a value when it is not "null".
     *
     * @param  array|string|int  $key
     * @param  mixed  $value
     * @return $this
     */
    public function fill(array|string|int $key, mixed $value): static
    {
        return $this->set($key, $value, false);
    }

    /**
     * Fills many keys with values when these are not "null".
     *
     * @param  array  $array
     * @return $this
     */
    public function fillMany(array $array): static
    {
        return $this->setMany($array, false);
    }

    /**
     * Check if the given keys are defined using "dot" notation.
     *
     * @param  string|int  ...$keys
     * @return bool
     */
    public function has(string|int ...$keys): bool
    {
        $missing = (object) [];

        foreach ($keys as $key) {
            if ($missing === $this->get($key, $missing)) {
                return false;
            }
        }

        return !empty($keys);
    }

    /**
     * Determine that at least one key is defined using "dot" notation.
     *
     * @param  string|int  ...$keys
     * @return bool
     */
    public function hasAny(string|int ...$keys): bool
    {
        if ($keys) {
            foreach ($keys as $key) {
                if ($this->has($key)) {
                    return true;
                }
            }

            return false;
        }

        return $this->isNotEmpty();
    }

    /**
     * Check if the given key is not defined using dot notation.
     *
     * @param  string|int  $key
     * @return bool
     */
    public function missing(string|int $key): bool
    {
        return !$this->has($key);
    }

    /**
     * Removes a JSON key.
     *
     * @param  string|int  $key
     * @return $this
     */
    public function forget(string|int $key): static
    {
        $segment = &$this->parameters;

        // @phpstan-ignore-next-line
        $keys = explode('.', $key) ?: [$key];

        foreach ($keys as $index => $name) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$index]);

            if (is_array($segment) && array_key_exists($name, $segment)) {
                $segment = &$segment[$name];
            } elseif (property_exists($segment, $name)) {
                $segment = &$segment->{$name};
            }
        }

        if (is_array($segment)) {
            unset($segment[array_shift($keys)]);
        } elseif (is_object($segment)) {
            unset($segment->{array_shift($keys)});
        }

        return $this;
    }

    /**
     * Removes a JSON key.
     *
     * @param  string|int  $key
     * @return $this
     * @codeCoverageIgnore
     */
    public function unset(string|int $key): static
    {
        return $this->forget($key);
    }

    /**
     * Determine a key is declared and its value is not "null".
     *
     * @param  string|int  $key
     * @return bool
     */
    public function isSet(string|int $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Determine a key is not declared or its value is "null".
     *
     * @param  string|int  $key
     * @return bool
     */
    public function isNotSet(string|int $key): bool
    {
        return !$this->isSet($key);
    }

    /**
     * Checks if the Json instance has no keys.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->parameters);
    }

    /**
     * Checks if the Json instance has at least one key.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Returns the parameter keys.
     *
     * @return array<int, string|int>
     */
    public function keys(): array
    {
        return array_keys($this->parameters);
    }

    /**
     * Returns an array of only the given root keys.
     *
     * @param  string|int  ...$keys
     * @return array
     */
    public function only(string|int ...$keys): array
    {
        return Arr::only($this->parameters, $keys);
    }

    /**
     * Returns an array of all the root keys except the issued ones.
     *
     * @param  string|int  ...$keys
     * @return array
     */
    public function except(string|int ...$keys): array
    {
        return Arr::except($this->parameters, $keys);
    }

    /**
     * Retrieves a segment of the JSON data as a new Json instance.
     *
     * Non-existent keys will be filled with a default value.
     *
     * @param  array|string|int  $segments
     * @param  mixed|null  $default
     * @return static
     */
    public function segments(array|string|int $segments, mixed $default = null): static
    {
        $json = new static();

        foreach ((array) $segments as $key) {
            $json->set($key, $this->get($key, $default));
        }

        return $json;
    }

    /**
     * Returns a Json instance as a Collection.
     *
     * @param  string|int|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function collect(string|int $key = null): Collection
    {
        return new Collection($this->get($key));
    }

    /**
     * Dynamically get JSON values.
     *
     * @param  string  $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Dynamically set JSON values.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Dynamically check a JSON key presence.
     *
     * @param  string  $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return $this->isSet($name);
    }

    /**
     * Dynamically unset a JSON key.
     *
     * @param  string  $name
     * @return void
     */
    public function __unset(string $name): void
    {
        $this->forget($name);
    }

    /**
     * Whether an offset exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->isSet($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->forget($offset);
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->parameters;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(static function ($value): mixed {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->parameters);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int  $options
     * @return false|string
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this);
    }

    /**
     * Create a new Json instance.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable  $json
     * @return static
     */
    public static function make(Arrayable|iterable $json = []): static
    {
        return new static($json);
    }

    /**
     * Wraps an array into a Json instance.
     *
     * @param  self|iterable|null  $json
     * @return static
     */
    public static function wrap(self|iterable|null $json): static
    {
        return $json instanceof static ? $json : new static($json);
    }

    /**
     * Create a new Json instance from a JSON string.
     *
     * @param  string  $json
     * @param  int  $depth
     * @param  int  $options
     * @return static
     */
    public static function fromJson(string $json, int $depth = 512, int $options = 0): static
    {
        return new static((array) json_decode($json, true, $depth, $options));
    }

    // This section below contains methods to bring compatibility for the ParameterBag
    // instance that is part of the Request input. This way we can swap the original
    // with this Json instance, bringing all the new features, inside the Request.

    /**
     * @inheritDoc
     */
    public function all(string $key = null): array
    {
        return $this->get($key);
    }

    /**
     * Adds parameters.
     *
     * @param  array  $parameters
     * @return void
     */
    public function add(array $parameters = []): void
    {
        $this->setMany($parameters, false);
    }

    /**
     * Removes a parameter.
     *
     * @param  string  $key
     * @return void
     */
    public function remove(string $key): void
    {
        $this->forget($key);
    }
}
