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
use Illuminate\Support\ServiceProvider;

class GeocoderService extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $configPath = __DIR__ . "/../../config/geocoder.php";
        $this->publishes(
            [$configPath => $this->configPath("geocoder.php")],
            "config"
        );
        $this->mergeConfigFrom($configPath, "geocoder");
    }

    public function register()
    {
        $this->app->alias("Geocoder", Geocoder::class);
        $this->app->singleton(ProviderAndDumperAggregator::class, function () {
            return (new ProviderAndDumperAggregator)
                ->registerProvidersFromConfig(collect(config("geocoder.providers")));
        });
        $this->app->bind('geocoder', ProviderAndDumperAggregator::class);
    }

    public function provides() : array
    {
        return ["geocoder", ProviderAndDumperAggregator::class];
    }

    protected function configPath(string $path = "") : string
    {
        if (function_exists("config_path")) {
            return config_path($path);
        }

        $pathParts = [
            app()->basePath(),
            "config",
            trim($path, "/"),
        ];

        return implode("/", $pathParts);
    }
}
