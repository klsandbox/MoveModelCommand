<?php

namespace Klsandbox\MoveModelCommand;

use Illuminate\Support\ServiceProvider;

class MoveModelCommandServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('command.klsandbox.editmovemodel', function ($app) {
            return new EditMoveModel();
        });
        $this->app->singleton('command.klsandbox.listmodel', function ($app) {
            return new ListModel();
        });

        $this->commands('command.klsandbox.editmovemodel');
        $this->commands('command.klsandbox.listmodel');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'command.klsandbox.editmovemodel',
            'command.klsandbox.listmodel',
        ];
    }
}
