<?php

namespace Agp\Login;

use Illuminate\Support\ServiceProvider;

class AgpLoginServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Login');
    }
}
