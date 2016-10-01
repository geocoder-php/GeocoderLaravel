<?php namespace Toin0u\GeocoderLaravel\Tests\Laravel5_3\Providers;

use Toin0u\GeocoderLaravel\Tests\Laravel5_3\TestCase;
use Toin0u\Geocoder\Exceptions\InvalidDumperException;
use Toin0u\Geocoder\ProviderAndDumperAggregator;
use Toin0u\Geocoder\GeocoderServiceProvider;
use Geocoder\Provider\Chain;
use Geocoder\Provider\FreeGeoIp;
use Geocoder\Provider\GoogleMaps;
use Ivory\HttpAdapter\CurlHttpAdapter;

class GeocoderServiceProviderTest extends TestCase
{
    public function testItResolvesAGivenAddress()
    {
        $result = app('geocoder')
            ->using('chain')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }

    public function testItResolvesAGivenIPAddress()
    {
        $result = app('geocoder')
            ->geocode('8.8.8.8')
            ->all();
        $this->assertEquals('US', $result[0]->getCountry()->getCode());
    }

    public function testItResolvesAGivenAddressWithUmlauts()
    {
        $result = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->all();
        $this->assertEquals('22', $result[0]->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $result[0]->getStreetName());
        $this->assertEquals('Wien', $result[0]->getLocality());
        $this->assertEquals('1020', $result[0]->getPostalCode());
    }

    public function testItCanUseMaxMindBinaryWithoutProvider()
    {
        $result = app('geocoder')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }

    public function testItCanUseASpecificProvider()
    {
        $result = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }

    public function testItDumpsAndAddress()
    {
        $result = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->dump('geojson');
        $jsonAddress = json_decode($result->first());

        $this->assertEquals('1600', $jsonAddress->properties->streetNumber);
    }

    public function testItThrowsAnExceptionForInvalidDumper()
    {
        $this->expectException(InvalidDumperException::class);
        $result = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->dump('test');
        $jsonAddress = json_decode($result->first());

        $this->assertEquals('1600', $jsonAddress->properties->streetNumber);
    }

    public function testConfig()
    {
        $this->assertTrue(is_array($providers = $this->app['config']->get('geocoder.providers')));
        $this->assertCount(2, $providers);
        $this->assertArrayHasKey(GoogleMaps::class, $providers[Chain::class]);
        $this->assertArrayHasKey(FreeGeoIp::class, $providers[Chain::class]);
        $this->assertSame(CurlHttpAdapter::class, $this->app['config']->get('geocoder.adapter'));
    }

    public function testLoadedProviders()
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(GeocoderServiceProvider::class, $loadedProviders);
        $this->assertTrue($loadedProviders[GeocoderServiceProvider::class]);
    }

    public function testGeocoder()
    {
        $this->assertInstanceOf(ProviderAndDumperAggregator::class, app('geocoder'));
    }
}
