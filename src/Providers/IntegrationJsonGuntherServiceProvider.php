<?php

namespace ConfrariaWeb\IntegrationJsonGunther\Providers;

use Illuminate\Support\ServiceProvider;
use ConfrariaWeb\IntegrationJsonGunther\Services\IntegrationJsonGuntherService;

class IntegrationJsonGuntherServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('IntegrationJsonGuntherService', function () {
            return new IntegrationJsonGuntherService();
        });
    }

}
