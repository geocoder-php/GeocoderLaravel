<?php namespace Geocoder\Laravel\Providers;

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Provider\Chain;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use ReflectionClass;

/**
 * Geocoder service provider
 *
 * @author Antoine Corcy <contact@sbin.dk>
 * @author Mike Bronner <hello@genealabs.com>
 */
class GeocoderService extends ServiceProvider
{
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../../config/geocoder.php';
        $this->publishes([$configPath => config_path('geocoder.php')], 'config');
        $this->mergeConfigFrom($configPath, 'geocoder');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->alias('Geocoder', Geocoder::class);
        $this->app->singleton('geocoder', function () {
            $geocoder = new ProviderAndDumperAggregator();
            $geocoder->registerProviders(
                $this->getProviders(collect(config('geocoder.providers')))
            );

            return $geocoder;
        });
    }

    /**
     * Instantiate the configured Providers, as well as the Chain Provider.
     *
     * @param Collection
     * @return array
     */
    private function getProviders(Collection $providers)
    {
        $providers = $providers->map(function ($arguments, $provider) {
            $arguments = $this->getArguments($arguments, $provider);
            $reflection = new ReflectionClass($provider);

            if ($provider === 'Geocoder\Provider\Chain') {
                return $reflection->newInstance($arguments);
            }

            return $reflection->newInstanceArgs($arguments);
        });

        return $providers->toArray();
    }

    /**
     * Insert the required Adapter instance (if required) as the first element
     * of the arguments array.
     *
     * @param array
     * @param string
     * @return string
     */
    private function getArguments(array $arguments, $provider)
    {
        if ($provider === 'Geocoder\Provider\Chain') {
            return $this->getProviders(
                collect(config('geocoder.providers.Geocoder\Provider\Chain'))
            );
        }

        $adapter = $this->getAdapterClass($provider);

        if ($adapter) {
            array_unshift($arguments, (new $adapter));
        }

        return $arguments;
    }

    /**
     * Get the required Adapter class name for the current provider. It will
     * select a specific adapter if required, handle the Chain provider, and
     * return the default configured adapter if non of the above are true.
     *
     * @param string
     * @return string
     */
    private function getAdapterClass($provider)
    {
        $specificAdapters = collect([
            'Geocoder\Provider\GeoIP2' => 'Geocoder\Adapter\GeoIP2Adapter',
            'Geocoder\Provider\MaxMindBinary' => null,
        ]);

        if ($specificAdapters->has($provider)) {
            return $specificAdapters->get($provider);
        }

        return config('geocoder.adapter');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['geocoder'];
    }
}
