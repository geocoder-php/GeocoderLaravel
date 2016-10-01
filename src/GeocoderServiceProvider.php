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

use Geocoder\ProviderAggregator;
use Geocoder\Provider\Chain;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

/**
 * Geocoder service provider
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/geocoder.php');

        $this->publishes([$source => config_path('geocoder.php')], 'config');

        $this->mergeConfigFrom($source, 'geocoder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geocoder.adapter', function ($app) {
            $adapter = config('geocoder.adapter');

            return new $adapter;
        });

        $this->app->singleton('geocoder.chain', function ($app) {
            $providers = collect(config('geocoder.providers'))
                ->map(function ($arguments, $provider) {
                    $reflection = new ReflectionClass($provider);

                    if (is_array($arguments)) {
                        array_unshift($arguments, $this->app['geocoder.adapter']);
                        return $reflection->newInstanceArgs($arguments);
                    }

                    return $reflection->newInstance($this->app['geocoder.adapter']);
                });

            return new Chain($providers->toArray());
        });

        $this->app->singleton('geocoder', function ($app) {
            $geocoder = new ProviderAggregator();
            $geocoder->registerProvider($app['geocoder.chain']);

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
        return ['geocoder', 'geocoder.adapter', 'geocoder.chain'];
    }
}
