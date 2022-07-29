<?php

namespace Poseidonphp\LaravelOidcClient;

use Illuminate\Support\ServiceProvider;


class LaravelOidcClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/oidc.php' => config_path('oidc.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // bindings

    }

    public function provides()
    {
        return [];
    }


}
