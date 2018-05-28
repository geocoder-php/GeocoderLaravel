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

        $providerAndDumperAggregator = (new ProviderAndDumperAggregator)
            ->registerProvidersFromConfig(collect(config("geocoder.providers")));

        $this->app->singleton("geocoder", function ($app) use ($providerAndDumperAggregator) {
            return $providerAndDumperAggregator;
        });

        // Resolve dependency via class name
        // i.e app(ProviderAndDumperAggregator::class) or _construct(ProviderAndDumperAggregator $geocoder)
        $this->app->instance(ProviderAndDumperAggregator::class, $providerAndDumperAggregator);
    }

    public function register()
    {
        $this->app->alias("Geocoder", Geocoder::class);
    }

    public function provides(): array
    {
        return ["geocoder"];
    }

    protected function configPath(string $path = ""): string
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
