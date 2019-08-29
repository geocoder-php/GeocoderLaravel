<?php namespace Geocoder\Laravel\Tests;

use Geocoder\Laravel\Providers\GeocoderService;

trait CreatesApplication
{
    public function setUp() : void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/database/factories');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getPackageProviders($app)
    {
        return [
            GeocoderService::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require(__DIR__ . '/config/testConfig.php');
        $app['config']->set('geocoder', $config);
        $app['config']->set('database.redis.default', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
        ]);
        $app['config']->set('database.redis.geocode-cache', [
            'host' => env('REDIS_HOST', '192.168.10.10'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 1,
        ]);
        $app['config']->set('cache.stores.geocode', [
            'driver' => 'redis',
            'connection' => 'geocode-cache',
        ]);
        $app['config']->set('geocoder.store', 'geocode');
    }
}
