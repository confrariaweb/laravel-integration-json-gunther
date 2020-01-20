<?php

namespace ConfrariaWeb\IntegrationJsonGunther\Providers;

use Illuminate\Support\ServiceProvider;
use ConfrariaWeb\IntegrationJsonGunther\Services\JsonGuntherService;

class IntegrationJsonGuntherServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('JsonGuntherService', function () {
            return new JsonGuntherService();
        });
    }

}
