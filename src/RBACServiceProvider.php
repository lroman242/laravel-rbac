<?php

namespace lroman242\LaravelRBAC;

use Illuminate\Support\ServiceProvider;

class RBACServiceProvider extends ServiceProvider
{
    /**
     * Indicates of loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider
     *
     * @return null
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/migrations' => $this->app->databasePath().'/migrations'
        ], 'migrations');

        $this->registerBladeDirectives();
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('rbac', function ($app) {
            $auth = $app->make('Illuminate\Contracts\Auth\Guard');

            return new \lroman242\LaravelRBAC\RBAC($auth);
        });
    }

    /**
     * Register the blade directives.
     *
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('can', function($expression) {
            return "<?php if (\\Shinobi::can({$expression})): ?>";
        });

        Blade::directive('endcan', function($expression) {
            return "<?php endif; ?>";
        });

        Blade::directive('canatleast', function($expression) {
            return "<?php if (\\Shinobi::canAtLeast({$expression})): ?>";
        });

        Blade::directive('endcanatleast', function($expression) {
            return "<?php endif; ?>";
        });

        Blade::directive('role', function($expression) {
            return "<?php if (\\Shinobi::is({$expression})): ?>";
        });

        Blade::directive('endrole', function($expression) {
            return "<?php endif; ?>";
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['rbac'];
    }
}