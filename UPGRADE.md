# Upgrade Guide

## Upgrading To 1.0 From 0.x

Notice: The package name has changed from `writingink/wink` to `themsaid/wink`.

To upgrade to version 1.0, you need to run the following commands

```sh
php artisan wink:migrate
php artisan vendor:publish --tag=wink-assets --force
```

In addition to this, make sure you render your posts and pages using `$post->content` and `$page->content` instead of `->body`.

## Laravel 11, 12, and 13

This release requires PHP 8.3+ and Laravel 11, 12, or 13. Install it from this fork (`themsaid/wink:dev-1.x`). Packagist `themsaid/wink` stops at Laravel 10.

`Wink\WinkAuthor` implements `getAuthPasswordName()` so the Laravel 11 authentication contract can resolve the password column. Route files import the `Route` facade directly, because a fresh Laravel 11+ application does not register global facade aliases.

### Published configuration

Laravel 11 and newer store the `local` filesystem disk in `storage/app/private`. Wink now defaults to the `public` disk and the `wink/images` path so `php artisan storage:link` can serve uploads. A previously published `config/wink.php` keeps the old defaults (`local` and `public/wink/images`) until you change them. Update the defaults in that file:

```php
'storage_disk' => env('WINK_STORAGE_DISK', 'public'),

'storage_path' => env('WINK_STORAGE_PATH', 'wink/images'),
```

Or set them in `.env`:

```
WINK_STORAGE_DISK=public
WINK_STORAGE_PATH=wink/images
```

Files uploaded with the previous defaults on Laravel 10 (`local` disk, path `public/wink/images`) already live in `storage/app/public/wink/images`, which is the same directory as the new defaults. If a Laravel 11+ app stored uploads under `storage/app/private/public/wink/images`, move that directory to `storage/app/public/wink/images`. Run `php artisan storage:link` if `public/storage` does not already point at `storage/app/public`.

Publish the `wink-assets` tag when you want fresh admin assets. Leave a customized `config/wink.php` in place. The `wink-config` tag overwrites that file.

After updating, run:

```sh
php artisan wink:migrate
php artisan vendor:publish --tag=wink-assets --force
```
