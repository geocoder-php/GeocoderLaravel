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
        $this->assertSame('Geocoder\Provider\FreeGeoIpProvider', $this->app['config']->get('geocoder-laravel::provider'));
        $this->assertSame('Geocoder\HttpAdapter\CurlHttpAdapter', $this->app['config']->get('geocoder-laravel::adapter'));
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

    public function testGeocoderDefaultProvider()
    {
        $this->assertInstanceOf('Geocoder\\Provider\\FreeGeoIpProvider', $this->app['geocoder.provider']);
    }

    public function testGeocoder()
    {
        $this->assertInstanceOf('Geocoder\\Geocoder', $this->app['geocoder']);
    }
}
