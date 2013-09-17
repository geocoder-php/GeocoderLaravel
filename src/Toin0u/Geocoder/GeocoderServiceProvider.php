<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Geocoder;

use Geocoder\Geocoder;
use Illuminate\Support\ServiceProvider;

/**
 * Geocoder service provider
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->package('toin0u/geocoder-laravel');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $this->app['geocoder.adapter'] = $this->app->share(function() {
            $adapter = \Config::get('geocoder-laravel::adapter');
            $class = 'Geocoder\HttpAdapter\\' . $adapter;
            return new $class;
        });

        $this->app['geocoder.provider'] = $this->app->share(function($app) {
    $provider = \Config::get('geocoder-laravel::provider');
            $class = '\Geocoder\Provider\\' . $provider;
            return new $class($app['geocoder.adapter']);
        });

        $this->app['geocoder'] = $this->app->share(function($app) {
            $geocoder = new Geocoder;
            $geocoder->registerProvider($app['geocoder.provider']);

            return $geocoder;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('geocoder');
    }
}
