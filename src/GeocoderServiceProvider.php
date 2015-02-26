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
use Geocoder\Provider\ChainProvider;

/**
 * Geocoder service provider
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../config/geocoder.php');

        $this->publishes([$source => config_path('geocoder.php')]);

        $this->mergeConfigFrom($source, 'geocoder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geocoder.adapter', function($app) {
            $adapter = $app['config']->get('geocoder.adapter');

            return new $adapter;
        });

        $this->app->singleton('geocoder.chain', function($app) {
            $providers = [];

            foreach($app['config']->get('geocoder.providers') as $provider => $arguments) {
                if (0 !== count($arguments)) {
                    $providers[] = call_user_func_array(
                        function ($arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null) use ($app, $provider) {
                            return new $provider($app['geocoder.adapter'], $arg1, $arg2, $arg3, $arg4);
                        },
                        $arguments
                    );

                    continue;
                }

                $providers[] = new $provider($app['geocoder.adapter']);
            }

            return new ChainProvider($providers);
        });

        $this->app['geocoder'] = $this->app->share(function($app) {
            $geocoder = new Geocoder;
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
