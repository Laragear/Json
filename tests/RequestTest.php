<?php

namespace Tests;

use Illuminate\Http\Request;
use Laragear\Json\Json;

class RequestTest extends TestCase
{
    public function test_macro_returns_json_instance(): void
    {
        $request = Request::create('/test', content: '{"foo":"bar"}');

        static::assertInstanceOf(Json::class, $request->getJson());
        static::assertSame(['foo' => 'bar'], $request->getJson()->all());
    }

    public function test_macro_returns_json_value(): void
    {
        $request = Request::create('/test', content: '{"foo":"bar"}');

        static::assertSame('bar', $request->getJson('foo'));
        static::assertSame('quz', $request->getJson('baz', 'quz'));
        static::assertNull($request->getJson('bar'));
    }

    public function test_macro_replaces_parameter_bag(): void
    {
        $request = Request::create('/test', content: '{"foo":"bar"}');

        // This makes the original ParameterBag.
        static::assertSame('bar', $request->json('foo'));

        static::assertInstanceOf(Json::class, $request->getJson());
        static::assertSame(['foo' => 'bar'], $request->getJson()->all());
        static::assertSame('bar', $request->getJson('foo'));
        static::assertSame('quz', $request->getJson('baz', 'quz'));
        static::assertNull($request->getJson('bar'));
    }
}
