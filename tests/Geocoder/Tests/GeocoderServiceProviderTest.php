<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Geocoder\Tests;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderServiceProviderTest extends TestCase
{
    public function testConfig()
    {
        $this->assertTrue(is_array($providers = $this->app['config']->get('geocoder-laravel::providers')));
        $this->assertContains('Geocoder\\Provider\\GoogleMapsProvider', $providers);
        $this->assertContains('Geocoder\\Provider\\FreeGeoIpProvider', $providers);
        $this->assertSame('Geocoder\\HttpAdapter\\CurlHttpAdapter', $this->app['config']->get('geocoder-laravel::adapter'));
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
        $this->assertInstanceOf('Geocoder\\Provider\\ChainProvider', $this->app['geocoder.provider']);
    }

    public function testGeocoderDefaultProvider()
    {
        $providers = $this->getProtectedProperty($this->app['geocoder.provider'], 'providers');

        $this->assertInstanceOf('Geocoder\\Provider\\GoogleMapsProvider', $providers[0]);
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
