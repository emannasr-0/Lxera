<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MicrosoftGraphService;

class MicrosoftGraphServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('microsoftgraph', function ($app) {
            return new MicrosoftGraphService();
        });
    }

    public function boot()
    {
        //
    }
}
