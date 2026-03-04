<?php

namespace Logicoforms\Forms\Tests;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Vite;
use Logicoforms\Forms\Contracts\FormOwner;
use Logicoforms\Forms\FormsServiceProvider;
use Logicoforms\Forms\Models\Form;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use DatabaseMigrations;

    protected function getPackageProviders($app): array
    {
        return [
            FormsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('forms.owner_model', TestUser::class);
        $app['config']->set('forms.register_web_routes', true);
        $app['config']->set('forms.auth_middleware', ['web']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Vite::useScriptTagAttributes([]);
        $this->withoutVite();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

    }

    protected function afterRefreshingDatabase(): void
    {
        // Create a minimal users table for testing
        $this->app['db']->connection()->getSchemaBuilder()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function createUser(array $attributes = []): TestUser
    {
        return TestUser::create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ], $attributes));
    }

    protected function createPublishedForm(array $attributes = [], ?TestUser $user = null): Form
    {
        $user ??= $this->createUser();

        return Form::create(array_merge([
            'title' => 'Test Form',
            'status' => 'published',
            'created_by' => $user->getKey(),
        ], $attributes));
    }

    protected function actingAsUser(?TestUser $user = null): TestUser
    {
        $user ??= $this->createUser();
        $this->actingAs($user);

        return $user;
    }
}

class TestUser extends Authenticatable implements FormOwner
{
    protected $table = 'users';
    protected $guarded = [];

    public function forms(): HasMany
    {
        return $this->hasMany(Form::class, 'created_by');
    }
}
