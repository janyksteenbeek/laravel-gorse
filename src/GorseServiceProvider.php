<?php

namespace JanykSteenbeek\LaravelGorse;

use Illuminate\Support\ServiceProvider;
use JanykSteenbeek\LaravelGorse\Client\GorseClient;
use JanykSteenbeek\LaravelGorse\Console\Commands\SyncItemsCommand;
use JanykSteenbeek\LaravelGorse\Console\Commands\SyncUsersCommand;
use JanykSteenbeek\LaravelGorse\Services\GorseService;

class GorseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/gorse.php', 'gorse'
        );

        $this->app->singleton(GorseClient::class, function ($app) {
            $config = $app['config']['gorse'];

            return new GorseClient(
                $config['endpoint'],
                $config['api_key'],
                $config['verify_ssl'] ?? true
            );
        });

        $this->app->singleton(GorseService::class, function ($app) {
            return new GorseService(
                $app->make(GorseClient::class),
                $app['config']['gorse']['resolving']['enabled'] ?? true
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/gorse.php' => config_path('gorse.php'),
            ], 'gorse-config');

            $this->commands([
                SyncUsersCommand::class,
                SyncItemsCommand::class,
            ]);
        }
    }
}
