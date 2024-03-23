<?php

namespace nickurt\PwnedPasswords;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../src/Resources/Lang', 'pwned-passwords');

        $this->publishes([
            __DIR__.'/../src/Resources/Lang' => resource_path('lang/vendor/pwned-passwords'),
        ], 'config');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['nickurt\PwnedPasswords\PwnedPasswords', 'PwnedPasswords'];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('nickurt\PwnedPasswords\PwnedPasswords', function ($app) {
            return new PwnedPasswords;
        });

        $this->app->alias('nickurt\PwnedPasswords\PwnedPasswords', 'PwnedPasswords');
    }
}
