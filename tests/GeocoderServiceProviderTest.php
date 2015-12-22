<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Tests\Geocoder;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderServiceProviderTest extends TestCase
{
    public function testConfig()
    {
        $this->assertTrue(is_array($providers = $this->app['config']->get('geocoder.providers')));
        $this->assertCount(3, $providers);
        $this->assertArrayHasKey('Geocoder\\Provider\\GoogleMaps', $providers);
        $this->assertArrayHasKey('Geocoder\\Provider\\FreeGeoIp', $providers);
        $this->assertSame('Ivory\\HttpAdapter\\CurlHttpAdapter', $this->app['config']->get('geocoder.adapter'));
    }

    public function testLoadedProviders()
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey('Toin0u\\Geocoder\\GeocoderServiceProvider', $loadedProviders);
        $this->assertTrue($loadedProviders['Toin0u\\Geocoder\\GeocoderServiceProvider']);
    }

    public function testGeocoderDefaultAdapter()
    {
        $this->assertInstanceOf('Ivory\\HttpAdapter\\CurlHttpAdapter', $this->app['geocoder.adapter']);
    }

    public function testGeocoderChainProvider()
    {
        $providers = $this->getProtectedProperty($this->app['geocoder'], 'providers');

        $this->assertArrayHasKey('chain', $providers);

        $this->assertInstanceOf('Geocoder\\Provider\\Chain', $providers['chain']);

        $chainProviders = $this->getProtectedProperty($providers['chain'], 'providers');

        $this->assertInstanceOf('Geocoder\\Provider\\GoogleMaps', $chainProviders[0]);
        $this->assertSame('fr-FR', $chainProviders[0]->getLocale());
        $this->assertInstanceOf('Ivory\\HttpAdapter\\CurlHttpAdapter', $chainProviders[0]->getAdapter());

        $this->assertInstanceOf('Geocoder\\Provider\\FreeGeoIp', $chainProviders[1]);
        $this->assertInstanceOf('Ivory\\HttpAdapter\\CurlHttpAdapter', $chainProviders[1]->getAdapter());

    }

    public function testGeocoderNamedProviders()
    {
        $providers = $this->getProtectedProperty($this->app['geocoder'], 'providers');

        $this->assertInstanceOf('Geocoder\\Provider\\GoogleMaps', $providers['google_maps']);
        $this->assertSame('fr-FR', $providers['google_maps']->getLocale());
        $this->assertInstanceOf('Ivory\\HttpAdapter\\CurlHttpAdapter', $providers['google_maps']->getAdapter());

        $this->assertInstanceOf('Geocoder\\Provider\\FreeGeoIp', $providers['free_geo_ip']);
        $this->assertInstanceOf('Ivory\\HttpAdapter\\CurlHttpAdapter', $providers['free_geo_ip']->getAdapter());
    }

    public function testGeocoder()
    {
        $this->assertInstanceOf('Geocoder\\Geocoder', $this->app['geocoder']);
    }

    protected function getProtectedProperty($testObj, $propertyName)
    {
        $reflection = new \ReflectionClass($testObj);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($testObj);
    }
}
