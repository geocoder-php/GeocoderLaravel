<?php namespace Geocoder\Laravel\Providers;

/**
 * This file is part of the Geocoder Laravel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Mike Bronner <hello@genealabs.com>
 * @license    MIT License
 */

use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;

class GeocoderService extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $configPath = __DIR__ . '/../../config/geocoder.php';
        $this->publishes([$configPath => config_path('geocoder.php')], 'config');
        $this->mergeConfigFrom($configPath, 'geocoder');
        $this->app->singleton('geocoder', function () {
            return (new ProviderAndDumperAggregator)->registerProviders(
                $this->getProviders(collect(config('geocoder.providers')))
            );
        });
    }

    public function register()
    {
        $this->app->alias('Geocoder', Geocoder::class);
    }

    /**
     * Instantiate the configured Providers, as well as the Chain Provider.
     */
    private function getProviders(Collection $providers) : array
    {
        $providers = $providers->map(function ($arguments, $provider) {
            $arguments = $this->getArguments($arguments, $provider);
            $reflection = new ReflectionClass($provider);

            if ($provider === 'Geocoder\Provider\Chain\Chain') {
                return $reflection->newInstance($arguments);
            }

            return $reflection->newInstanceArgs($arguments);
        });

        return $providers->toArray();
    }

    /**
     * Insert the required Adapter instance (if required) as the first element
     * of the arguments array.
     */
    private function getArguments(array $arguments, string $provider) : array
    {
        if ($provider === 'Geocoder\Provider\Chain\Chain') {
            return $this->getProviders(
                collect(config('geocoder.providers.Geocoder\Provider\Chain\Chain'))
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
     */
    private function getAdapterClass(string $provider) : string
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

    public function provides() : array
    {
        return ['geocoder'];
    }
}
