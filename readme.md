<p align="center"><img src="/art/header.png?1" alt="wink logo"></p>

Wink adds a nice UI where you can manage a publication of any size with posts, pages, tags, and authors.

You can add photos, code blocks, featured images, social media & SEO attributes, embedded HTML (YouTube Videos, Embedded Podcasts Episodes, Tweets, ...), and markdown!

Wink is used to manage the [official Laravel blog](https://blog.laravel.com), [divinglaravel.com](https://divinglaravel.com), and many more.

Dark & Light modes available so everyone is happy 😁

## Requirements

- PHP 8.3, 8.4, or 8.5
- Laravel 11, 12, or 13

## Installation

Wink uses a separate database connection and authentication system so that you don't have to modify any of your project code.

Packagist `themsaid/wink` stops at Laravel 10. Install this fork from GitHub. Add a VCS repository to your application's `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/drscript/wink"
    }
]
```

Then run these commands in the root of your Laravel app:

```sh
composer require themsaid/wink:dev-1.x
php artisan wink:install
php artisan storage:link
```

**Configure the database connection** wink is going to be using in `config/wink.php`. That file reads `WINK_DB_CONNECTION` and defaults to a connection named `wink`. Point it at a connection you already have, or add a `wink` connection in `config/database.php`. Then run:

```sh
php artisan wink:migrate
```

Head to `yourproject.test/wink` and use the provided email and password to log in.

Uploaded images use the `public` disk and the `wink/images` path, so files land in `storage/app/public/wink/images`. `storage:link` serves that directory. On Laravel 11 and newer the `local` disk points at `storage/app/private`, so leave `storage_disk` set to `public` unless you intend to use a private or S3 disk.

## Uploading to S3

If you want to upload images to S3, update the `storage_disk` attribute in your `wink.php` configuration file to s3. Make sure your S3 disk is correctly configured in your `filesystems.php` configuration file.

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('CDN_URL'),
    'options' => [
        'CacheControl' => 'public, max-age=315360000'
    ],
],
```

Note: you're going to need to install the AWS-S3 Flysystem adapter, using `composer require league/flysystem-aws-s3-v3` for this to work.

## Using Unsplash

Visit https://unsplash.com/oauth/applications to create a new unsplash app. Grab the 'Access Key' and add it to your `.env` file as `UNSPLASH_ACCESS_KEY`. Lastly, add unsplash to your `config/services.php` file:

```php
'unsplash' => [
    'key' => env('UNSPLASH_ACCESS_KEY'),
],
```

## Updates

After each update, make sure you run these commands:

```sh
php artisan wink:migrate
php artisan vendor:publish --tag=wink-assets --force
```

Leave `config/wink.php` in place if you have already customized it. Publishing the `wink-config` tag overwrites that file.

## Upgrade notes

Coming from older Wink or Laravel 10? This fork requires PHP 8.3+ and Laravel 11, 12, or 13. Require `themsaid/wink:dev-1.x` from this repository. `composer require themsaid/wink` without the VCS repository installs the Packagist release, which stops at Laravel 10.

A previously published `config/wink.php` keeps the old upload defaults (`local` disk and `public/wink/images`) until you change them. Update the defaults in that file:

```php
'storage_disk' => env('WINK_STORAGE_DISK', 'public'),

'storage_path' => env('WINK_STORAGE_PATH', 'wink/images'),
```

Or set them in `.env`:

```
WINK_STORAGE_DISK=public
WINK_STORAGE_PATH=wink/images
```

On Laravel 10 those old defaults already stored files in `storage/app/public/wink/images`. That is the same directory the new defaults use, so updating the config is enough.

On Laravel 11 and newer the `local` disk root is `storage/app/private`. If uploads landed in `storage/app/private/public/wink/images`, move that folder to `storage/app/public/wink/images`. Run `php artisan storage:link` if `public/storage` does not already point at `storage/app/public`.

Then run the update commands above. More detail is in the [upgrade guide](UPGRADE.md).

## Displaying your content

Wink is faceless, it doesn't have any opinions on how you display your content in your frontend. You can use the wink models in your controllers to display the different resources:

- `Wink\WinkPost`
- `Wink\WinkPage`
- `Wink\WinkAuthor`
- `Wink\WinkTag`

To display posts and pages content, use `$post->content` instead of `$post->body`. The content will always be in HTML format while the body might be HTML or raw markdown based on the post type.

## Credits

- [Mohamed Said](https://github.com/themsaid)
- [All contributors](https://github.com/drscript/wink/contributors)

Special thanks to [Caneco](https://twitter.com/caneco) for the logo ✨

## Contributing

Check the [contribution guide](CONTRIBUTING.md).

## License

Wink is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
