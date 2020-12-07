<?php

namespace SocketBus;

use Illuminate\Support\ServiceProvider;
use Illuminate\Broadcasting\BroadcastManager;

class SocketBusProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->resolving(BroadcastManager::class, function($broadcastManager, $app){
            $broadcastManager->extend('socketbus', function($app, $settings){
                return new SocketBusLaravelDriver($settings);
            });
        });

        $this->app->singleton(SocketBusLaravelDriver::class, function($app) {
            $settings = config('broadcasting.connections.socketbus');
            return new SocketBusLaravelDriver($settings);
        });

        $this->app->alias(SocketBusLaravelDriver::class, 'socketbus');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
