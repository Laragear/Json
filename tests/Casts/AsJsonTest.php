<?php

namespace Tests\Casts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Laragear\Json\Casts\AsEncryptedJson;
use Laragear\Json\Casts\AsJson;
use Laragear\Json\Json;
use Mockery;
use Orchestra\Testbench\TestCase;

class AsJsonTest extends TestCase
{
    use RefreshDatabase;

    protected TestUser $user;

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
    }

    protected function afterRefreshingDatabase(): void
    {
        $this->user = new TestUser([
            'name' => 'john',
            'email' => 'john@email.com',
            'password' => 'test_password',
            'options' => [],
            'encrypted_options' => [],
        ]);

        Schema::table('users', static function (Blueprint $table): void {
            $table->json('options')->default('');
            $table->json('nullable_options')->nullable();

            $table->json('encrypted_options')->default('');
            $table->json('nullable_encrypted_options')->nullable();

            $table->json('castable')->nullable();
        });
    }

    public function test_caches_json_object_in_model(): void
    {
        $this->user->options = ['foo' => 'bar'];

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'options' => '{"foo":"bar"}'
        ]);

        $this->user->options->set('baz', 'quz');

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'options' => '{"foo":"bar","baz":"quz"}'
        ]);
    }

    public function test_dynamically_casts_json_to_json_string(): void
    {
        $this->user->castable = Json::make(['foo' => 'bar']);

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'castable' => '{"foo":"bar"}'
        ]);
    }

    public function test_casts_json_into_database(): void
    {
        $this->user->options = ['foo' => 'bar'];

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'options' => '{"foo":"bar"}'
        ]);

        $user = TestUser::find(1);

        static::assertInstanceOf(Json::class, $user->options);
        static::assertSame(['foo' => 'bar'], $user->options->all());
    }

    public function test_casts_null_into_nullable_json_column(): void
    {
        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'nullable_options' => null,
            'nullable_encrypted_options' => null,
        ]);
    }

    public function test_casts_encrypted_json_into_database(): void
    {
        Crypt::expects('encrypt')
            ->twice()
            ->with(Mockery::type(Json::class), false)
            ->andReturn('encrypted_string');

        Crypt::expects('decrypt')
            ->with('encrypted_string', false)
            ->andReturn('{"foo":"bar"}');

        $this->user->encrypted_options = ['foo' => 'bar'];

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'encrypted_options' => 'encrypted_string'
        ]);

        $user = TestUser::find(1);

        static::assertInstanceOf(Json::class, $user->encrypted_options);
        static::assertSame(['foo' => 'bar'], $user->encrypted_options->all());
    }

    public function test_sets_null_over_encrypted_json(): void
    {
        Crypt::expects('encrypt')->never();
        Crypt::expects('decrypt')->never();

        $this->user->nullable_encrypted_options = null;

        $this->user->save();

        $this->assertDatabaseHas(TestUser::class, [
            'id' => 1,
            'nullable_encrypted_options' => null
        ]);

        $user = TestUser::find(1);

        static::assertNull($user->nullable_encrypted_options);
    }
}

/**
 * @method  \Tests\Casts\TestUser find($id)
 * @property \Laragear\Json\Json $options
 * @property \Laragear\Json\Json|null $nullable_options
 * @property \Laragear\Json\Json $encrypted_options
 * @property \Laragear\Json\Json|null $nullable_encrypted_options
 */
class TestUser extends User
{
    protected static $unguarded = true;
    protected $table = 'users';
    protected $casts = [
        'options' => AsJson::class,
        'nullable_options' => AsJson::class,
        'encrypted_options' => AsEncryptedJson::class,
        'nullable_encrypted_options' => AsEncryptedJson::class,
    ];
}
