<?php namespace Geocoder\Laravel\Tests;

use Orchestra\Database\ConsoleServiceProvider;
use Geocoder\Laravel\Providers\GeocoderService;

trait CreatesApplication
{
    public function setUp()
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
            ConsoleServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require(__DIR__ . '/config/testConfig.php');
        $app['config']->set('geocoder', $config);
    }
}
