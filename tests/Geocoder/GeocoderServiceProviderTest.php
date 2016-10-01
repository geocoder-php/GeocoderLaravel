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
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey('Geocoder\\Provider\\GoogleMapsProvider', $providers);
        $this->assertArrayHasKey('Geocoder\\Provider\\FreeGeoIpProvider', $providers);
        $this->assertSame('Geocoder\\HttpAdapter\\CurlHttpAdapter', $this->app['config']->get('geocoder.adapter'));
    }

    public function testLoadedProviders()
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey('Toin0u\\Geocoder\\GeocoderServiceProvider', $loadedProviders);
        $this->assertTrue($loadedProviders['Toin0u\\Geocoder\\GeocoderServiceProvider']);
    }

    public function testGeocoderDefaultAdapter()
    {
        $this->assertInstanceOf('Geocoder\\HttpAdapter\\CurlHttpAdapter', $this->app['geocoder.adapter']);
    }

    public function testGeocoderChainProvider()
    {
        $this->assertInstanceOf('Geocoder\\Provider\\ChainProvider', $this->app['geocoder.chain']);
    }

    public function testGeocoderDefaultProvider()
    {
        $providers = $this->getProtectedProperty($this->app['geocoder.chain'], 'providers');

        $this->assertInstanceOf('Geocoder\\Provider\\GoogleMapsProvider', $providers[0]);
        $this->assertSame('fr-FR', $providers[0]->getLocale());
        $this->assertInstanceOf('Geocoder\\HttpAdapter\\CurlHttpAdapter', $providers[0]->getAdapter());

        $this->assertInstanceOf('Geocoder\\Provider\\FreeGeoIpProvider', $providers[1]);
        $this->assertNull($providers[1]->getLocale());
        $this->assertInstanceOf('Geocoder\\HttpAdapter\\CurlHttpAdapter', $providers[1]->getAdapter());
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
