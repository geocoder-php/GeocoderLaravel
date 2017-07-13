<?php namespace Geocoder\Laravel\Tests\Laravel5_3\Providers;

use Geocoder\Laravel\Tests\Laravel5_3\TestCase;
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Laravel\Providers\GeocoderService;
use Geocoder\Provider\Chain;
use Geocoder\Provider\FreeGeoIp;
use Geocoder\Provider\GoogleMaps;
use Geocoder\Provider\MaxMindBinary;
use Geocoder\Exception\FunctionNotFound;
use Ivory\HttpAdapter\CurlHttpAdapter;

class GeocoderServiceTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        app()->register(GeocoderService::class);
    }

    public function testItReverseGeocodesCoordinates()
    {
        // Arrange

        // Act
        $result = app('geocoder')->reverse(38.8791981, -76.9818437)->all();

        // Assert
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20003', $result[0]->getPostalCode());
    }

    public function testItResolvesAGivenAddress()
    {
        // Arrange

        // Act
        $result = app('geocoder')
            ->using('chain')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();

        // Assert
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20500', $result[0]->getPostalCode());
    }

    public function testItResolvesAGivenIPAddress()
    {
        // Arrange

        // Act
        $result = app('geocoder')
            ->geocode('8.8.8.8')
            ->all();

        // Assert
        $this->assertEquals('US', $result[0]->getCountry()->getCode());
    }

    public function testItResolvesAGivenAddressWithUmlauts()
    {
        // Arrange

        // Act
        $result = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->all();

        // Assert
        $this->assertEquals('22', $result[0]->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $result[0]->getStreetName());
        $this->assertEquals('Wien', $result[0]->getLocality());
        $this->assertEquals('1020', $result[0]->getPostalCode());
    }

    public function testItResolvesAGivenAddressWithUmlautsInRegion()
    {
        // Arrange
        config()->set('geocoder.providers.Geocoder\Provider\Chain.Geocoder\Provider\GoogleMaps', [
            'de-DE',
            'Wien, Österreich',
            true,
            null,
        ]);
        app()->register(GeocoderService::class);

        // Act
        $result = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->all();

        // Assert
        $this->assertEquals('22', $result[0]->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $result[0]->getStreetName());
        $this->assertEquals('Wien', $result[0]->getLocality());
        $this->assertEquals('1020', $result[0]->getPostalCode());
    }

    public function testItCanUseMaxMindBinaryWithoutProvider()
    {
        //Arrange
        $this->expectException(FunctionNotFound::class);
        $provider = new MaxMindBinary('dummy');

        // Act
        app('geocoder')->registerProvider($provider);

        // Assert
        // By getting past the constructor parameters requirements, we know we
        // are instantiating the provider correctly.
    }

    public function testItCanUseASpecificProvider()
    {
        $result = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->all();
        $this->assertEquals('1600', $result[0]->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $result[0]->getStreetName());
        $this->assertEquals('Washington', $result[0]->getLocality());
        $this->assertEquals('20500', $result[0]->getPostalCode());
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
        $this->assertCount(3, $providers);
        $this->assertArrayHasKey(GoogleMaps::class, $providers[Chain::class]);
        $this->assertArrayHasKey(FreeGeoIp::class, $providers[Chain::class]);
        $this->assertSame(CurlHttpAdapter::class, $this->app['config']->get('geocoder.adapter'));
    }

    public function testLoadedProviders()
    {
        $loadedProviders = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(GeocoderService::class, $loadedProviders);
        $this->assertTrue($loadedProviders[GeocoderService::class]);
    }

    public function testGeocoder()
    {
        $this->assertInstanceOf(ProviderAndDumperAggregator::class, app('geocoder'));
    }

    public function testCacheIsUsed()
    {
        $result = app('geocoder')->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->get();
        $cacheKey = str_slug(strtolower(urlencode('1600 Pennsylvania Ave., Washington, DC USA')));

        $this->assertEquals($result, app('cache')->get("geocoder-{$cacheKey}"));
        $this->assertTrue(app('cache')->has("geocoder-{$cacheKey}"));
    }

    public function testJapaneseCharacterGeocoding()
    {
        $cacheKey = str_slug(strtolower(urlencode('108-0075 東京都港区港南２丁目１６－３')));

        app('geocoder')->geocode('108-0075 東京都港区港南２丁目１６－３')
            ->get();

        $this->assertEquals($cacheKey, '108-0075e69db1e4baace983bde6b8afe58cbae6b8afe58d97efbc92e4b881e79baeefbc91efbc96efbc8defbc93');
        $this->assertTrue(app('cache')->has("geocoder-{$cacheKey}"));
    }

    public function testFailedGeocodingCanBeCaught()
    {
        $result = 'success';

        try {
            app('geocoder')
                ->geocode('asd,Afghanistan')
                ->get();
        } catch (\Throwable $exception) {
            $result = 'failure';
        }

        $this->assertEquals('failure', $result);
    }
}
