<?php

namespace Salehhashemi\OtpManager;

use Illuminate\Support\ServiceProvider;

class OtpManagerServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'otp-manager');

        // Publishing the lang file.
        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/otp-manager'),
        ], 'lang');

        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/otp.php' => config_path('otp.php'),
        ], 'config');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/otp.php', 'otp');
    }
}
