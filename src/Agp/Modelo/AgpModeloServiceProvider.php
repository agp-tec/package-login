<?php

namespace Agp\Modelo;

use Illuminate\Support\ServiceProvider;

class AgpModeloServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Modelo');
    }
}