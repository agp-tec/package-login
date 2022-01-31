<?php

namespace Agp\Login;

use Illuminate\Support\ServiceProvider;

class AgpLoginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        $this->publishes([
            __DIR__ . '/config/login.php' => config_path('login.php'),
        ], 'config');
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Login');
    }
}
