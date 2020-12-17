<?php

namespace Statamic\Addons\WordpressUsers;

use Statamic\Addons\WordpressUsers\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $scripts = [
        __DIR__.'/../resources/js/app.js'
    ];
    
    protected $stylesheets = [
        __DIR__.'/../resources/css/app.css'
    ];
    
    public function boot()
    {
        parent::boot();

        $this->overrideUserProvider();

        $this->registerPermission();

        $this->extendNavigation();
    }

    private function overrideUserProvider()
    {
        $this->app->booted(function () {
            Auth::provider('statamic', function () {
                return new UserProvider;
            });
        });
    }

    private function registerPermission()
    {
        $this->app->booted(function () {
            Permission::register('access wordpress-users addon')
                ->label('Access the WordPress Users addon');
        });
    }

    private function extendNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->create('WordPress Users')
                ->section('Users')
                ->icon('users-box')
                ->route('wordpress-users.index')
                ->can('access wordpress-users addon');
        });
    }
}
