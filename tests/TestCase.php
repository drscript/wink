<?php

namespace Wink\Tests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase as Orchestra;
use Wink\WinkAuthor;
use Wink\WinkPost;
use Wink\WinkServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! is_file(public_path('vendor/wink/mix-manifest.json'))) {
            $this->artisan('vendor:publish', [
                '--tag' => 'wink-assets',
                '--force' => true,
            ]);
        }

        $this->artisan('migrate', [
            '--database' => 'wink',
            '--path' => realpath(__DIR__.'/../src/Migrations'),
            '--realpath' => true,
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            WinkServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('app.key', 'base64:YWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWFhYWE=');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('database.connections.wink', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('wink.database_connection', 'wink');
        $app['config']->set('wink.storage_disk', 'public');
        $app['config']->set('wink.storage_path', 'wink/images');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('filesystems.disks.public', [
            'driver' => 'local',
            'root' => storage_path('framework/testing/disks/public'),
            'url' => 'http://localhost/storage',
            'visibility' => 'public',
        ]);
    }

    protected function author(array $overrides = []): WinkAuthor
    {
        return WinkAuthor::create(array_merge([
            'id' => (string) Str::uuid(),
            'name' => 'Regina Phalange',
            'slug' => 'regina-phalange',
            'email' => 'admin@mail.com',
            'password' => Hash::make('secret'),
            'bio' => 'This is me.',
        ], $overrides));
    }

    protected function postFor(WinkAuthor $author, array $overrides = []): WinkPost
    {
        return WinkPost::create(array_merge([
            'id' => (string) Str::uuid(),
            'slug' => 'hello-world',
            'title' => 'Hello World',
            'excerpt' => 'An excerpt',
            'body' => 'The body',
            'published' => true,
            'markdown' => false,
            'publish_date' => now()->subMinute(),
            'featured_image_caption' => '',
            'author_id' => $author->id,
        ], $overrides));
    }
}
