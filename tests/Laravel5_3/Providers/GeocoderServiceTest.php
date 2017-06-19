<?php namespace Geocoder\Laravel\Tests\Laravel5_3\Providers;

use Geocoder\Exception\FunctionNotFound;
use Geocoder\Laravel\Tests\Laravel5_3\TestCase;
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Laravel\Providers\GeocoderService;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\FreeGeoIp\FreeGeoIp;
use Geocoder\Provider\MaxMindBinary\MaxMindBinary;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Model\Coordinates;
use Http\Client\Curl\Client as CurlAdapter;
use Illuminate\Support\Collection;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
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
        $results = app('geocoder')->reverse(38.8791981, -76.9818437)->get();

        // Assert
        $this->assertEquals('1600', $results->first()->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Southeast', $results->first()->getStreetName());
        $this->assertEquals('Washington', $results->first()->getLocality());
        $this->assertEquals('20003', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItResolvesAGivenAddress()
    {
        // Arrange

        // Act
        $results = app('geocoder')
            ->using('chain')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->get();

        // Assert
        $this->assertEquals('1600', $results->first()->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $results->first()->getStreetName());
        $this->assertEquals('Washington', $results->first()->getLocality());
        $this->assertEquals('20500', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItResolvesAGivenIPAddress()
    {
        // Arrange

        // Act
        $results = app('geocoder')
            ->geocode('8.8.8.8')
            ->get();

        // Assert
        $this->assertEquals('US', $results->first()->getCountry()->getCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItResolvesAGivenAddressWithUmlauts()
    {
        // Arrange

        // Act
        $results = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->get();

        // Assert
        $this->assertEquals('22', $results->first()->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $results->first()->getStreetName());
        $this->assertEquals('Wien', $results->first()->getLocality());
        $this->assertEquals('1020', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItResolvesAGivenAddressWithUmlautsInRegion()
    {
        // Arrange
        config()->set('geocoder.providers.Geocoder\Provider\Chain\Chain.Geocoder\Provider\GoogleMaps\GoogleMaps', [
            'de-DE',
            null,
        ]);
        app()->register(GeocoderService::class);

        // Act
        $results = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->get();

        // Assert
        $this->assertEquals('22', $results->first()->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $results->first()->getStreetName());
        $this->assertEquals('Wien', $results->first()->getLocality());
        $this->assertEquals('1020', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItCanUseASpecificProvider()
    {
        $results = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->get();
        $this->assertEquals('1600', $results->first()->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $results->first()->getStreetName());
        $this->assertEquals('Washington', $results->first()->getLocality());
        $this->assertEquals('20500', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItDumpsAndAddress()
    {
        $results = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->dump('geojson');
        $jsonAddress = json_decode($results->first());

        $this->assertEquals('1600', $jsonAddress->properties->streetNumber);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItThrowsAnExceptionForInvalidDumper()
    {
        $this->expectException(InvalidDumperException::class);
        $results = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->dump('test');
        $jsonAddress = json_decode($results->first());

        $this->assertEquals('1600', $jsonAddress->properties->streetNumber);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testConfig()
    {
        $this->assertEquals(999999999, config('geocoder.cache-duraction'));
        $this->assertTrue(is_array($providers = $this->app['config']->get('geocoder.providers')));
        $this->assertCount(3, $providers);
        $this->assertArrayHasKey(GoogleMaps::class, $providers[Chain::class]);
        $this->assertArrayHasKey(FreeGeoIp::class, $providers[Chain::class]);
        $this->assertSame(CurlAdapter::class, $this->app['config']->get('geocoder.adapter'));
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
        $cacheKey = 'geocoder-' . str_slug('1600 Pennsylvania Ave., Washington, DC USA');

        $this->assertTrue(cache()->has($cacheKey));
        $this->assertEquals($result, cache($cacheKey));
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testGeocodeQueryProvidesResults()
    {
        $query = GeocodeQuery::create('1600 Pennsylvania Ave., Washington, DC USA');

        $results = app('geocoder')->geocodeQuery($query)->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertTrue($results->isNotEmpty());
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testReverseQueryProvidesResults()
    {
        $coordinates = new Coordinates(38.8791981, -76.9818437);
        $query = ReverseQuery::create($coordinates);

        $results = app('geocoder')->reverseQuery($query)->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testFacadeProvidesResults()
    {
        $results = Geocoder::geocode('1600 Pennsylvania Ave., Washington, DC USA')->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItCanUseMaxMindBinaryWithoutProvider()
    {
        $provider = new MaxMindBinary(__DIR__ . '/../../assets/GeoIP.dat');

        app('geocoder')->registerProvider($provider);
    }
}
