<?php

namespace ConfrariaWeb\IntegrationJsonGunther\Providers;

use Illuminate\Support\ServiceProvider;
use MeridienClube\Meridien\Services\Integrations\JsonGuntherService;

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
