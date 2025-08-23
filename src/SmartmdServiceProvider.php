<?php

namespace NoisyWinds\Smartmd;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use NoisyWinds\Smartmd\Facades\Smartmd as SmartmdFacade;

/**
 * Laravel service provider for the Smartmd package.
 * 
 * Handles registration and bootstrapping of the Smartmd markdown editor
 * for Laravel applications with version 11+ and 12+ compatibility.
 * 
 * @package NoisyWinds\Smartmd
 * @author noisywinds
 * @since 2.0.0
 */
class SmartmdServiceProvider extends ServiceProvider
{
    /**
     * All the container singletons that should be registered.
     *
     * @var array<string, string>
     */
    public array $singletons = [
        'smartmd' => Smartmd::class,
    ];

    /**
     * Bootstrap any application services.
     *
     * This method is called after all other service providers have been registered,
     * meaning you have access to all other services that have been registered.
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function boot(): void
    {
        $this->bootViews();
        $this->bootPublishing();
        $this->bootConfig();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConfig();
        $this->registerServices();
    }

    /**
     * Boot view registration.
     *
     * @return void
     */
    protected function bootViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/views', 'Smartmd');
    }

    /**
     * Boot publishing of assets and configuration.
     *
     * @return void
     */
    protected function bootPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/views' => $this->app->resourcePath('views/vendor/smartmd'),
            ], 'smartmd-views');

            $this->publishes([
                __DIR__ . '/static' => $this->app->publicPath('vendor/laravel-smartmd'),
            ], 'smartmd-assets');

            $this->publishes([
                __DIR__ . '/config/smartmd.php' => $this->app->configPath('smartmd.php'),
            ], 'smartmd-config');

            $this->publishes([
                __DIR__ . '/Controller' => $this->app->path('Http/Controllers/Smartmd'),
            ], 'smartmd-controllers');

            // Publish everything with a single tag
            $this->publishes([
                __DIR__ . '/views' => $this->app->resourcePath('views/vendor/smartmd'),
                __DIR__ . '/static' => $this->app->publicPath('vendor/laravel-smartmd'),
                __DIR__ . '/config/smartmd.php' => $this->app->configPath('smartmd.php'),
                __DIR__ . '/Controller' => $this->app->path('Http/Controllers/Smartmd'),
            ], 'smartmd');
        }
    }

    /**
     * Boot configuration merging.
     *
     * @return void
     */
    protected function bootConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/smartmd.php', 'smartmd');
    }

    /**
     * Register configuration.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/smartmd.php', 'smartmd');
    }

    /**
     * Register package services.
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $this->app->singleton('smartmd', function (Application $app): Smartmd {
            return new Smartmd($app['config']);
        });

        $this->app->alias('smartmd', Smartmd::class);
        $this->app->alias('smartmd', SmartmdFacade::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            'smartmd',
            Smartmd::class,
            SmartmdFacade::class,
        ];
    }
}
