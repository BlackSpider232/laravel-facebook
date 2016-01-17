<?php

/*
 * This file is part of Laravel Facebook.
 *
  * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Facebook;

use Facebook\Facebook;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;

/**
 * This is the Facebook service provider class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig($this->app);
    }

    /**
     * Setup the config.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function setupConfig(Application $app)
    {
        $source = realpath(__DIR__.'/../config/facebook.php');

        if ($app instanceof LaravelApplication && $app->runningInConsole()) {
            $this->publishes([$source => config_path('facebook.php')]);
        } elseif ($app instanceof LumenApplication) {
            $app->configure('facebook');
        }

        $this->mergeConfigFrom($source, 'facebook');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFactory($this->app);
        $this->registerManager($this->app);
        $this->registerBindings($this->app);
    }

    /**
     * Register the factory class.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function registerFactory(Application $app)
    {
        $app->singleton('facebook.factory', function () {
            return new FacebookFactory();
        });

        $app->alias('facebook.factory', FacebookFactory::class);
    }

    /**
     * Register the manager class.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function registerManager(Application $app)
    {
        $app->singleton('facebook', function ($app) {
            $config = $app['config'];
            $factory = $app['facebook.factory'];

            return new FacebookManager($config, $factory);
        });

        $app->alias('facebook', FacebookManager::class);
    }

    /**
     * Register the bindings.
     *
     * @param \Illuminate\Contracts\Container\Container $app
     *
     * @return void
     */
    protected function registerBindings(Application $app)
    {
        $app->bind('facebook.connection', function ($app) {
            $manager = $app['facebook'];

            return $manager->connection();
        });

        $app->alias('facebook.connection', Facebook::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'facebook',
            'facebook.factory',
            'facebook.connection',
        ];
    }
}
