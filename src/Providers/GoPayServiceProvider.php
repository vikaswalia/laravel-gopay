<?php

namespace VikasWalia\LaravelGoPay\Providers;

use Illuminate\Support\ServiceProvider;
use VikasWalia\LaravelGoPay\GoPaySDK;

class GoPayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/gopay.php' => config_path('gopay.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        if(is_dir($vendor = __DIR__.'/../../vendor')){
            require_once $vendor.'/autoload.php';
        }

        $this->mergeConfigFrom(
            __DIR__.'/../config/gopay.php', 'gopay'
        );

        $this->app->singleton('GoPaySDK', function ($app) {
            return new GoPaySDK();
        });
    }
}