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
use function func_get_args;
use function func_num_args;
use function is_array;
use function is_object;
use function json_decode;
use function json_encode;
use function property_exists;

class Json extends ParameterBag implements Stringable, ArrayAccess, JsonSerializable, Arrayable, Jsonable, Responsable
{
    use Conditionable;
    use Tappable;

    /**
     * Create a new Json instance.
     *
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    final public function __construct(Arrayable|iterable $parameters = [])
    {
        $this->parameters = [];

        if ($parameters instanceof Arrayable) {
            $parameters = $parameters->toArray();
        }

        foreach ($parameters as $key => $value) {
            $this->parameters[$key] = $value;
        }
    }

    /**
     * Returns a JSON value in "dot" notation.
     */
    public function get(array|string|int|null $key, mixed $default = null): mixed
    {
        return data_get($this->parameters, $key, $default);
    }

    /**
     * Returns many values of the JSON as a single array.
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
     */
    public function set(string $key, mixed $value): void
    {
        data_set($this->parameters, $key, $value);
    }

    /**
     * Sets many JSON values in "dot" notation.
     *
     * @return $this
     */
    public function setMany(array $keys): static
    {
        foreach ($keys as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Fills a key with a value when it is not "null".
     *
     * @return $this
     */
    public function fill(array|string|int $key, mixed $value): static
    {
        data_set($this->parameters, $key, $value, false);

        return $this;
    }

    /**
     * Fills many keys with values when these are not "null".
     *
     * @return $this
     */
    public function fillMany(array $keys): static
    {
        foreach ($keys as $key => $value) {
            $this->fill($key, $value);
        }

        return $this;
    }

    /**
     * Returns true if the parameter is defined.
     */
    public function has(string $key): bool
    {
        $key = func_num_args() > 1 ? func_get_args() : [$key];

        $missing = (object) [];

        foreach ($key as $value) {
            if ($missing === $this->get($value, $missing)) {
                return false;
            }
        }

        return !empty($key);
    }

    /**
     * Determine that at least one key is defined using "dot" notation.
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
     */
    public function missing(string|int $key): bool
    {
        return !$this->has($key);
    }

    /**
     * Removes a JSON key.
     *
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
     * @return $this
     * @codeCoverageIgnore
     */
    public function unset(string|int $key): static
    {
        return $this->forget($key);
    }

    /**
     * Determine a key is declared and its value is not "null".
     */
    public function isSet(string|int $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Determine a key is not declared or its value is "null".
     */
    public function isNotSet(string|int $key): bool
    {
        return !$this->isSet($key);
    }

    /**
     * Checks if the Json instance has no keys.
     */
    public function isEmpty(): bool
    {
        return empty($this->parameters);
    }

    /**
     * Checks if the Json instance has at least one key.
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
     */
    public function only(string|int ...$keys): array
    {
        return Arr::only($this->parameters, $keys);
    }

    /**
     * Returns an array of all the root keys except the issued ones.
     */
    public function except(string|int ...$keys): array
    {
        return Arr::except($this->parameters, $keys);
    }

    /**
     * Retrieves a segment of the JSON data as a new Json instance.
     *
     * Non-existent keys will be filled with a default value.
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
     */
    public function collect(string|int $key = null): Collection
    {
        return new Collection($this->get($key));
    }

    /**
     * Dynamically get JSON values.
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    /**
     * Dynamically set JSON values.
     */
    public function __set(string $name, mixed $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Dynamically check a JSON key presence.
     */
    public function __isset(string $name): bool
    {
        return $this->isSet($name);
    }

    /**
     * Dynamically unset a JSON key.
     */
    public function __unset(string $name): void
    {
        $this->forget($name);
    }

    /**
     * Whether an offset exists.
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->isSet($offset);
    }

    /**
     * Offset to retrieve.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset.
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->forget($offset);
    }

    /**
     * Returns a string representation of the object.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->parameters;
    }

    /**
     * Get the instance as an array.
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
     */
    public function toJson($options = 0): false|string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): JsonResponse
    {
        return new JsonResponse($this);
    }

    /**
     * Create a new Json instance.
     */
    public static function make(Arrayable|iterable $json = []): static
    {
        return new static($json);
    }

    /**
     * Wraps an array into a Json instance.
     */
    public static function wrap(self|iterable|null $json): static
    {
        return $json instanceof static ? $json : new static($json);
    }

    /**
     * Create a new Json instance from a JSON string.
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
        return (array) $this->get($key);
    }

    /**
     * @inheritDoc
     */
    public function add(array $parameters = []): void
    {
        $this->setMany($parameters);
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): void
    {
        $this->forget($key);
    }
}
