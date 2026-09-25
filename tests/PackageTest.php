<?php

namespace Wink\Tests;

use Illuminate\Support\Facades\Hash;
use Wink\WinkAuthor;

class PackageTest extends TestCase
{
    public function test_wink_guard_and_routes_are_registered(): void
    {
        $this->assertSame(WinkAuthor::class, config('auth.providers.wink_authors.model'));
        $this->assertSame('wink_authors', config('auth.guards.wink.provider'));

        $login = app('router')->getRoutes()->getByName('wink.auth.login');

        $this->assertNotNull($login);
        $this->assertNull($login->getDomain());
        $this->assertSame('wink/login', $login->uri());
        $this->assertNotNull(app('router')->getRoutes()->getByName('wink.posts.index'));
    }

    public function test_migrations_create_wink_tables_on_the_wink_connection(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::connection('wink')->hasTable('wink_posts'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::connection('wink')->hasTable('wink_authors'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::connection('wink')->hasTable('wink_tags'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::connection('wink')->hasTable('wink_pages'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::connection('sqlite')->hasTable('wink_posts'));
    }

    public function test_install_command_publishes_assets_and_config(): void
    {
        $this->artisan('wink:install')->assertSuccessful();

        $this->assertFileExists(public_path('vendor/wink/mix-manifest.json'));
        $this->assertFileExists(config_path('wink.php'));
    }

    public function test_migrate_command_creates_the_default_author(): void
    {
        $this->artisan('wink:migrate')
            ->expectsOutputToContain('admin@mail.com')
            ->assertSuccessful();

        $author = WinkAuthor::first();

        $this->assertNotNull($author);
        $this->assertSame('admin@mail.com', $author->email);
        $this->assertSame('password', $author->getAuthPasswordName());
        $this->assertTrue(Hash::isHashed($author->password));

        $this->artisan('wink:migrate')->assertSuccessful();
        $this->assertSame(1, WinkAuthor::count());
    }
}
