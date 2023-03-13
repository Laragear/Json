<?php /** @noinspection JsonEncodingApiUsageInspection */

namespace Tests;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Laragear\Json\Json;
use PHPUnit\Framework\TestCase;
use function array_merge;
use function json_encode;

class JsonTest extends TestCase
{
    protected const DATA = [
        'foo' => 'bar',
        'baz' => [
            'quz' => ['qux'],
            'quuz' => [
                'quux' => 'fred',
            ],
        ],
        'corge' => 'thud',
        'null' => null,
    ];

    protected Json $json;

    protected function setUp(): void
    {
        parent::setUp();

        $this->json = new Json(static::DATA);
    }

    public function test_get(): void
    {
        static::assertSame('fred', $this->json->get('baz.quuz.quux'));
        static::assertNull($this->json->get('invalid'));
        static::assertSame('foo', $this->json->get('invalid', 'foo'));
        static::assertSame('foo', $this->json->get('invalid', fn() => 'foo'));
    }

    public function test_get_many(): void
    {
        static::assertSame(
            ['foo' => 'bar', 'baz.quuz.quux' => 'fred', 'invalid' => null],
            $this->json->getMany(['foo', 'baz.quuz.quux', 'invalid'])
        );

        static::assertSame(
            ['foo' => 'bar', 'baz.quuz.quux' => 'fred', 'invalid' => 'bar'],
            $this->json->getMany(['foo', 'baz.quuz.quux', 'invalid'], 'bar')
        );

        static::assertSame(
            ['foo' => 'bar', 'baz.quuz.quux' => 'fred', 'invalid' => 'bar'],
            $this->json->getMany(['foo', 'baz.quuz.quux', 'invalid'], fn() => 'bar')
        );
    }

    public function test_set(): void
    {
        $this->json->set('foo', 'quz');
        static::assertSame('quz', $this->json->get('foo'));

        $this->json->set('baz.quuz.quux', 'corge');
        static::assertSame('corge', $this->json->get('baz.quuz.quux'));
    }

    public function test_set_many(): void
    {
        $this->json->setMany([
            'foo' => 'quz',
            'baz.quuz.quux' => 'corge',
            'fred' => 'thud'
        ]);

        static::assertSame('quz', $this->json->get('foo'));
        static::assertSame('corge', $this->json->get('baz.quuz.quux'));
        static::assertSame('thud', $this->json->get('fred'));
    }

    public function test_fill(): void
    {
        $this->json->fill('foo', 'quz');
        static::assertSame('bar', $this->json->get('foo'));

        $this->json->fill('baz.quuz.quux', 'corge');
        static::assertSame('fred', $this->json->get('baz.quuz.quux'));
    }

    public function test_fill_many(): void
    {
        $this->json->fillMany([
            'foo' => 'quz',
            'baz.quuz.quux' => 'corge',
            'fred' => 'thud'
        ]);
        static::assertSame('bar', $this->json->get('foo'));
        static::assertSame('fred', $this->json->get('baz.quuz.quux'));
        static::assertSame('thud', $this->json->get('fred'));
    }

    public function test_has(): void
    {
        static::assertTrue($this->json->has('foo'));
        static::assertTrue($this->json->has('foo', 'baz.quuz.quux'));
        static::assertTrue($this->json->has('foo', 'baz.quuz.quux', 'null'));
        static::assertTrue($this->json->has('null'));
        static::assertFalse($this->json->has('fred'));
        static::assertFalse($this->json->has('fred', 'baz.quuz.quux', 'null'));
    }

    public function test_has_any(): void
    {
        static::assertTrue($this->json->hasAny('foo'));
        static::assertTrue($this->json->hasAny('foo', 'baz.quuz.quux'));
        static::assertTrue($this->json->hasAny('foo', 'baz.quuz.quux', 'null'));
        static::assertTrue($this->json->hasAny('null'));
        static::assertFalse($this->json->hasAny('fred'));
        static::assertTrue($this->json->hasAny('fred', 'baz.quuz.quux', 'null'));

        static::assertTrue($this->json->hasAny());
    }

    public function test_missing(): void
    {
        static::assertFalse($this->json->missing('foo'));
        static::assertFalse($this->json->missing('baz.quuz.quux'));
        static::assertFalse($this->json->missing('null'));
        static::assertTrue($this->json->missing('fred'));
    }

    public function test_forget(): void
    {
        $this->json->forget('foo');
        static::assertFalse($this->json->has('foo'));

        $this->json->forget('baz.quuz.quux');
        static::assertFalse($this->json->has('baz.quuz.quux'));
    }

    public function test_forget_with_object(): void
    {
        $this->json->set('bar', (object) ['baz' => (object) ['quz' => 'qux']]);

        $this->json->forget('bar.baz.quz');
        static::assertFalse($this->json->has('bar.baz.quz'));
    }

    public function test_forgets_not_applied_to_value(): void
    {
        $this->json->forget('foo.bar');
        static::assertSame('bar', $this->json->get('foo'));
    }

    public function test_is_set(): void
    {
        static::assertTrue($this->json->isSet('foo'));
        static::assertTrue($this->json->isSet('baz.quuz.quux'));
        static::assertFalse($this->json->isSet('null'));
    }

    public function test_is_not_set(): void
    {
        static::assertFalse($this->json->isNotSet('foo'));
        static::assertFalse($this->json->isNotSet('baz.quuz.quux'));
        static::assertTrue($this->json->isNotSet('null'));
    }

    public function test_is_empty(): void
    {
        static::assertTrue(Json::make()->isEmpty());
        static::assertFalse(Json::make(['foo' => 'bar'])->isEmpty());
    }

    public function test_is_not_empty(): void
    {
        static::assertFalse(Json::make()->isNotEmpty());
        static::assertTrue(Json::make(['foo' => 'bar'])->isNotEmpty());
    }

    public function test_keys(): void
    {
        static::assertSame(['foo', 'baz', 'corge', 'null'], $this->json->keys());
    }

    public function test_only(): void
    {
        static::assertSame([
            'foo' => 'bar',
            'corge' => 'thud',
        ], $this->json->only('foo', 'corge', 'baz.quuz.quux'));
    }

    public function test_except(): void
    {
        static::assertSame([
            'foo' => 'bar',
            'corge' => 'thud',
        ], $this->json->except('baz', 'null', 'baz.quuz.quux'));
    }

    public function test_segment(): void
    {
        static::assertSame([
            'foo' => 'bar',
            'corge' => 'thud',
            'baz' => [
                'quuz' => [
                    'quux' => 'fred',
                ],
            ],
            'invalid' => null,
        ], $this->json->segments(['foo', 'corge', 'baz.quuz.quux', 'invalid'])->all());

        static::assertSame([
            'foo' => 'bar',
            'corge' => 'thud',
            'baz' => [
                'quuz' => [
                    'quux' => 'fred',
                ],
            ],
            'invalid' => 'foo',
        ], $this->json->segments(['foo', 'corge', 'baz.quuz.quux', 'invalid'], 'foo')->all());

        static::assertSame([
            'foo' => 'bar',
            'corge' => 'thud',
            'baz' => [
                'quuz' => [
                    'quux' => 'fred',
                ],
            ],
            'invalid' => 'foo',
        ], $this->json->segments(['foo', 'corge', 'baz.quuz.quux', 'invalid'], fn() => 'foo')->all());
    }

    public function test_collect(): void
    {
        $collection = $this->json->collect();

        static::assertSame(static::DATA, $collection->all());

        $collection = $this->json->collect('foo');

        static::assertSame('bar', $collection->first());
    }

    public function test_dynamic_access(): void
    {
        static::assertSame('bar', $this->json->foo);

        $this->json->bar = 'quz';
        static::assertSame('quz', $this->json->bar);

        static::assertTrue(isset($this->json->bar));
        static::assertFalse(isset($this->json->null));
        static::assertFalse(isset($this->json->invalid));

        unset($this->json->foo);

        static::assertTrue($this->json->missing('foo'));
    }

    public function test_array_access(): void
    {
        static::assertSame('bar', $this->json['foo']);

        $this->json['bar'] = 'quz';
        static::assertSame('quz', $this->json['bar']);

        static::assertTrue(isset($this->json['bar']));
        static::assertFalse(isset($this->json['null']));
        static::assertFalse(isset($this->json['invalid']));

        unset($this->json['foo']);

        static::assertTrue($this->json->missing('foo'));
    }

    public function test_to_string(): void
    {
        static::assertSame(
            '{"foo":"bar","baz":{"quz":["qux"],"quuz":{"quux":"fred"}},"corge":"thud","null":null}',
            (string) $this->json
        );
    }

    public function test_json_serializable(): void
    {
        static::assertSame(
            '{"foo":"bar","baz":{"quz":["qux"],"quuz":{"quux":"fred"}},"corge":"thud","null":null}',
            json_encode($this->json)
        );
    }

    public function test_to_array(): void
    {
        $this->json->set('arrayable', new class implements Arrayable {
            public function toArray() { return ['foo' => 'bar']; }
        });

        static::assertSame(
            array_merge(static::DATA, ['arrayable' => ['foo' => 'bar']]),
            $this->json->toArray()
        );
    }

    public function test_to_json(): void
    {
        static::assertSame(
            '{"foo":"bar","baz":{"quz":["qux"],"quuz":{"quux":"fred"}},"corge":"thud","null":null}',
            $this->json->toJson()
        );
    }

    public function test_to_response(): void
    {
        static::assertSame(
            '{"foo":"bar","baz":{"quz":["qux"],"quuz":{"quux":"fred"}},"corge":"thud","null":null}',
            $this->json->toResponse(new Request())->getContent()
        );
    }

    public function test_make(): void
    {
        static::assertEmpty(Json::make()->all());
        static::assertSame(['foo' => 'bar'], Json::make(['foo' => 'bar'])->all());
    }

    public function test_wrap(): void
    {
        $json = Json::wrap($array = ['foo' => 'bar']);

        static::assertSame($array, $json->all());
        static::assertSame($json, Json::wrap($json));
    }

    public function test_from_arrayable(): void
    {
        $json = Json::make(new class implements Arrayable {
            public function toArray()
            {
                return ['foo' => 'bar'];
            }
        });

        static::assertSame(['foo' => 'bar'], $json->all());
    }

    public function test_from_json(): void
    {
        static::assertSame(
            static::DATA,
            Json::fromJson(
                '{"foo":"bar","baz":{"quz":["qux"],"quuz":{"quux":"fred"}},"corge":"thud","null":null}'
            )->all()
        );
    }

    public function test_parameter_bag_all(): void
    {
        static::assertSame(static::DATA, $this->json->all());
        static::assertSame(['quux' => 'fred'], $this->json->all('baz.quuz'));
    }

    public function test_parameter_bag_replace(): void
    {
        $this->json->replace(['foo' => 'bar']);

        static::assertSame(['foo' => 'bar'], $this->json->all());
    }

    public function test_parameter_bag_add(): void
    {
        $this->json->add(['foo' => 'quz']);

        static::assertSame(array_merge(static::DATA, ['foo' => 'quz']), $this->json->all());
    }

    public function test_parameter_bag_remove(): void
    {
        $this->json->remove('foo');
        static::assertFalse($this->json->has('foo'));

        $this->json->remove('baz.quuz.quux');
        static::assertFalse($this->json->has('baz.quuz.quux'));
    }
}
