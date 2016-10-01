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
                    $arguments = $this->prepArguments($arguments, $provider);
                    $reflection = new ReflectionClass($provider);

                    return $reflection->newInstanceArgs($arguments);
                });

            return new Chain($providers->toArray());
        });

        $this->app->singleton('geocoder', function ($app) {
            $geocoder = new ProviderAggregator();
            $geocoder->registerProvider($app['geocoder.chain']);

            return $geocoder;
        });
    }

    private function prepArguments(array $arguments, $provider)
    {
        $specificAdapter = $this->providerRequiresSpecificAdapter($provider);

        if ($specificAdapter) {
            array_unshift($arguments, $specificAdapter);

            return $arguments;
        }

        if ($this->providerRequiresAdapter($provider)) {
            array_unshift($arguments, $this->app['geocoder.adapter']);

            return $arguments;
        }

        return $arguments;
    }

    private function providerRequiresSpecificAdapter($provider)
    {
        $specificAdapters = collect([
            'Geocoder\Provider\GeoIP2' => 'Geocoder\Adapter\GeoIP2Adapter',
        ]);

        return $specificAdapters->get($provider);
    }

    private function providerRequiresAdapter($provider)
    {
        $providersRequiringAdapter = collect([
            'Geocoder\Provider\ArcGISOnline',
            'Geocoder\Provider\BingMaps',
            'Geocoder\Provider\FreeGeoIp',
            'Geocoder\Provider\GeoIPs',
            'Geocoder\Provider\Geonames',
            'Geocoder\Provider\GeoPlugin',
            'Geocoder\Provider\GoogleMaps',
            'Geocoder\Provider\GoogleMapsBusiness',
            'Geocoder\Provider\HostIp',
            'Geocoder\Provider\IpInfoDb',
            'Geocoder\Provider\MapQuest',
            'Geocoder\Provider\MaxMind',
            'Geocoder\Provider\Nominatim',
            'Geocoder\Provider\OpenCage',
            'Geocoder\Provider\OpenStreetMap',
            'Geocoder\Provider\Provider',
            'Geocoder\Provider\TomTom',
            'Geocoder\Provider\Yandex',
        ]);

        return $providersRequiringAdapter->contains($provider);
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
