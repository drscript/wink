<?php

namespace Wink;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Wink\Http\Controllers\ForgotPasswordController;
use Wink\Http\Controllers\LoginController;
use Wink\Http\Middleware\Authenticate;

class WinkServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerAuthGuard();
        $this->registerPublishing();

        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'wink'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        $middlewareGroup = config('wink.middleware_group');

        $this->routeGroup($middlewareGroup)->group(function () {
            Route::get('/login', [LoginController::class, 'showLoginForm'])->name('auth.login');
            Route::post('/login', [LoginController::class, 'login'])->name('auth.attempt');

            Route::get('/password/forgot', [ForgotPasswordController::class, 'showResetRequestForm'])->name('password.forgot');
            Route::post('/password/forgot', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
            Route::get('/password/reset/{token}', [ForgotPasswordController::class, 'showNewPassword'])->name('password.reset');
        });

        $this->routeGroup([$middlewareGroup, Authenticate::class])->group(function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Start a Wink route group.
     *
     * An empty domain is omitted so routes stay host-agnostic. Laravel 13
     * prioritizes routes that declare a domain during matching.
     *
     * @param  string|array  $middleware
     * @return \Illuminate\Routing\RouteRegistrar
     */
    private function routeGroup($middleware)
    {
        $routes = Route::middleware($middleware)
            ->as('wink.')
            ->prefix(config('wink.path'));

        if ($domain = config('wink.domain')) {
            $routes->domain($domain);
        }

        return $routes;
    }

    /**
     * Register the package's authentication guard.
     *
     * @return void
     */
    private function registerAuthGuard()
    {
        $this->app['config']->set('auth.providers.wink_authors', [
            'driver' => 'eloquent',
            'model' => WinkAuthor::class,
        ]);

        $this->app['config']->set('auth.guards.wink', [
            'driver' => 'session',
            'provider' => 'wink_authors',
        ]);
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/wink'),
            ], 'wink-assets');

            $this->publishes([
                __DIR__.'/../config/wink.php' => config_path('wink.php'),
            ], 'wink-config');
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/wink.php', 'wink'
        );

        $this->commands([
            Console\InstallCommand::class,
            Console\MigrateCommand::class,
        ]);
    }
}
