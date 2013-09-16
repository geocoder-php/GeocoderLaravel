<?php

/**
 * This file is part of the Geocoder-laravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Geocoder;

use Geocoder\Geocoder;
use Geocoder\Provider\FreeGeoIpProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;
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
        $this->package('willdurand/geocoder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['geocoder.adapter'] = $this->app->share(function() {
            return new CurlHttpAdapter;
        });

        $this->app['geocoder.provider'] = $this->app->share(function() {
            return new FreeGeoIpProvider($this->app['geocoder.adapter']);
        });

        $this->app['geocoder'] = $this->app->share(function() {
            $geocoder = new Geocoder;
            $geocoder->registerProvider($this->app['geocoder.provider']);

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
