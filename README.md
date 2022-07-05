# JSON Transport

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laragear/json.svg)](https://packagist.org/packages/laragear/json)
[![Latest stable test run](https://github.com/Laragear/Json/workflows/Tests/badge.svg)](https://github.com/Laragear/MailLogin/actions)
[![Codecov coverage](https://codecov.io/gh/Laragear/Json/branch/1.x/graph/badge.svg?token=TODO:TOKEN)](https://codecov.io/gh/Laragear/Json)
[![CodeClimate Maintainability](https://api.codeclimate.com/v1/badges/fb8c25c8dea86186e1f0/maintainability)](https://codeclimate.com/github/Laragear/Json/maintainability)
[![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=Laragear_Json&metric=alert_status)](https://sonarcloud.io/dashboard?id=Laragear_Json)
[![Laravel Octane Compatibility](https://img.shields.io/badge/Laravel%20Octane-Compatible-success?style=flat&logo=laravel)](https://laravel.com/docs/9.x/octane#introduction)

Easily retrieve and manipulate `Json` across your application.

```php
use Laragear\Json\Json;

$json = Json::fromJson('{"foo":"bar"}');

$json->set('bar.quz', 'quz');

echo $json->foo; // "quz"
```

## Keep this package free

[![Patreon](.assets/patreon.png)](https://patreon.com/packagesforlaravel)[![Ko-fi](.assets/ko-fi.png)](https://ko-fi.com/DarkGhostHunter)[![Buymeacoffee](.assets/buymeacoffee.png)](https://www.buymeacoffee.com/darkghosthunter)[![PayPal](.assets/paypal.png)](https://www.paypal.com/paypalme/darkghosthunter)

Your support allows me to keep this package free, up-to-date and maintainable. Alternatively, you can **[spread the word!](http://twitter.com/share?text=I%20am%20using%20this%20cool%20PHP%20package&url=https://github.com%2FLaragear%2FJson&hashtags=PHP,Laravel)**

## Requirements

* Laravel 9.x or later
* PHP 8.0 or later

## Installation

Fire up Composer and require this package in your project.

    composer require laragear/json

That's it.

## Why is this for?

If you feel cumbersome to build complex JSON responses, or to deal with JSON trees values back and forth, this package is for you. It comes with a lot of features to make not only building and manipulating JSON trees.

```php
// Before
$name = $json['users'][1]['name'] ?? null;

// After
$name = $json->get('users.1.name');
```

## From the HTTP Request

Simply use `getJson()` to automatically retrieve all the JSON from the request, or a given key value.

```php
use Illuminate\Http\Request;
use Laragear\Json\Json;

public function data(Request $request)
{
    // Get the request JSON.
    $json = $request->getJson();
    
    // Return a key value from the JSON.
    $value = $json->get('this.is');
    
    // Set a value and return it.
    return $json->set('this.is', 'awesome');
}
```

> You can still use the `json()` method to retrieve the JSON data as a `ParameterBag` or a key value.

## As a HTTP Response

You can build a `Json` instance using `make()`, optionally with your own `array`, but you can also use anything that implements the `Arrayable` contract or is _iterable_. Since the `Json` instance implements the `Responsable` trait, you can return it as-is and will be automatically transformed into a [JSON response](https://laravel.com/docs/9.x/responses#json-responses).

```php
use Laragear\Json\Json;

public function send()
{
    return Json::make(['this_is' => 'cool']);
}
```

You can also transform it into a response and modify the outgoing response parameters, like the header or the status.

```php
use Laragear\Json\Json;

$response = Json::make(['this_is' => 'cool'])->toResponse();

$response->header('Content-Version', '1.0');
```

## Creating an instance

If you already have the `array` you want to transform into JSON, use the `make()` method or just instantiate it manually. Either way is fine. You can also use `Arrayable` objects and anything that is `iterable`, like [Collections](https://laravel.com/docs/9.x/collections).

```php
use Laragear\Json\Json;

$json = new Json([
    'users' => [
        'id' => 1,
        'name' => 'John',
    ]   
]);
```

If the value is already a JSON string, use `fromJson()`.

```php
use Laragear\Json\Json;

$json = Json::fromJson('{"users":{"id":1,"name":"John"}}');
```

## Available methods

The `Json` instance contains multiple helpful methods to make dealing with JSON data a breeze:

| Method                                         | Description                                                                                  |
|------------------------------------------------|----------------------------------------------------------------------------------------------|
| `get($key, $default = null)`                   | Retrieves a key value in "dot" notation, returning a default value if its not set.           |
| `getMany([$key => $value], $default = null)`   | Retrieves an array of values keyed "dot" notation, filling a default value on those not set. |
| `set($key, $value, $overwrite = true)`         | Sets a key in "dot" notation with a value.                                                   |
| `setMany([$key => $value], $overwrite = true)` | Sets multiple keys in "dot" notation with their respective values.                           |
| `fill($key, $value)`                           | Sets a key in "dot" notation with a value if the key doesn't exists or is `null`.            |
| `fillMany([$key => $value])`                   | Sets multiple keys in "dot" notation with their respective values if they don't exist.       |
| `has($key)`                                    | Check if a key in "dot" notation is defined in the JSON.                                     |
| `hasAny(...$keys)`                             | Check if at least one of the keys in "dot" notation is defined in the JSON.                  |
| `missing($key)`                                | Check if a key in "dot" notation is not defined in the JSON.                                 |
| `forget($key)`                                 | Removes (forgets) a key in "dot" notation from the JSON.                                     |
| `unset($key)`                                  | Removes (forgets) a key in "dot" notation from the JSON.                                     |
| `isSet($key)`                                  | Check if a key in "dot" notation exists and is not `null`.                                   |
| `isNotSet($key)`                               | Check if a key in "dot" notation doesn't exists or is not `null`.                            |
| `is($key)`                                     | Creates a condition to evaluate.                                                             |                                     |
| `isEmpty()`                                    | Check if the JSON data is empty.                                                             |
| `isNotEmpty()`                                 | Check if the JSON data is not empty.                                                         |
| `keys()`                                       | Returns an array of all the root-level keys of the JSON.                                     |
| `only(...$keys)`                               | Returns an array of only the issued root-level keys of the JSON.                             |
| `except(...$keys)`                             | Returns an array of all the root-level keys of the JSON except those issued.                 |
| `segments($keys, $default = null)`             | Returns a `Json` instance a segment of the JSON data using keys in "dot" notation.           |
| `collect($key = null)`                         | Returns a `Collection` instance from the JSON data, or value of a key in "dot" notation.     |
| `make($value = [])`                            | Creates a new `Json` instance from an `iterable` or `Arrayable`value.                        |
| `wrap($value)`                                 | Returns a `Json` instance from an `iterable` or `Arrayable` value if it's not `Json`.        |
| `fromJson($string)`                            | Returns a `Json` instance from a JSON-encoded string.                                        |

## Eloquent JSON Cast

When dealing with JSON attributes in models, you will note that is really cumbersome to work with. Instead of using arrays or Collections, you can use the `AsJson` and `AsEncryptedJson` casts, that will offer a `Json` instance from the model property as-is or encrypted into the database, respectively.

Just add one of these to [your model casts](https://laravel.com/docs/9.x/eloquent-mutators#custom-casts).

```php
<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
use Laragear\Json\Casts\AsJson; 
use Laragear\Json\Casts\AsEncryptedJson; 
 
class User extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options' => AsJson::class,
        'secure_options' => AsEncryptedJson::class,
    ];
}
```

Once done, you can easily fill JSON into your model like normal.

```php
use App\Models\User;
use Laragear\Json\Json;

$user = User::find();

// Set a Json instance, like from a string. 
$user->options = Json::fromJson('{"apples":"tasty"}')

// Or just directly use an array tree.
$user->secure_options = [
    'visa' => [
        ['last_4' => '1234', 'preferred' => true] 
    ]
];

// You can use the Json instance like a normal monday.
$user->secure_options->get('visa.last_4'); // "1234" 
```

## [Conditions](https://github.com/Laragear/Compare)

Condition handling through the `is()` method is handled by the [Laragear Compare package](https://github.com/Laragear/Compare).

```php
if ($json->is('apples')->not()->exactly('tasty')) {
    return 'The apples are not tasty!';
}
```

## PhpStorm stubs

For users of PhpStorm, there is a stub file to aid in macro autocompletion for this package. You can publish them using the `phpstorm` tag:

```shell
php artisan vendor:publish --provider="Laragear\Json\JsonServiceProvider" --tag="phpstorm"
```

The file gets published into the `.stubs` folder of your project. You should point your [PhpStorm to these stubs](https://www.jetbrains.com/help/phpstorm/php.html#advanced-settings-area).

## Laravel Octane compatibility

- There are no singletons using a stale application instance.
- There are no singletons using a stale config instance.
- There are no singletons using a stale request instance.
- There are no static properties written.

There should be no problems using this package with Laravel Octane.

## Security

If you discover any security related issues, please email darkghosthunter@gmail.com instead of using the issue tracker.

# License

This specific package version is licensed under the terms of the [MIT License](LICENSE.md), at time of publishing.

[Laravel](https://laravel.com) is a Trademark of [Taylor Otwell](https://github.com/TaylorOtwell/). Copyright © 2011-2022 Laravel LLC.
