<?php namespace Geocoder\Laravel\Tests;

use Geocoder\Laravel\Providers\GeocoderService;

trait CreatesApplication
{
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
        $app['config']->set('cache.stores.geocode', [
            'driver' => 'array',
            'serialize' => false,
        ]);
        $app['config']->set('geocoder.cache.store', 'geocode');
    }
}
