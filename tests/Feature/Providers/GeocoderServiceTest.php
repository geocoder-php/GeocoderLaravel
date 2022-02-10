<?php namespace Geocoder\Laravel\Tests\Feature\Providers;

use Illuminate\Support\Str;
use Geocoder\Model\Coordinates;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Provider\Chain\Chain;
use Illuminate\Support\Collection;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Laravel\Tests\FeatureTestCase;
use Http\Client\Curl\Client as CurlAdapter;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Laravel\Providers\GeocoderService;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Provider\MaxMindBinary\MaxMindBinary;
use Geocoder\Provider\GoogleMaps\Model\GoogleAddress;
use Geocoder\Laravel\Exceptions\InvalidDumperException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class GeocoderServiceTest extends FeatureTestCase
{
    // public function testItReverseGeocodesCoordinates()
    // {
    //     $result = app('geocoder')
    //         ->reverse(38.897957, -77.036560)
    //         ->get()
    //         ->filter(function (GoogleAddress $address) {
    //             return Str::contains($address->getStreetName() ?? '', 'Northwest');
    //         })
    //         ->first();

    //     $this->assertNotNull($result);
    //     $this->assertEquals('1600', $result->getStreetNumber());
    //     $this->assertEquals('Pennsylvania Avenue Northwest', $result->getStreetName());
    //     $this->assertEquals('Washington', $result->getLocality());
    //     $this->assertEquals('20500', $result->getPostalCode());
    // }

    public function testItResolvesAGivenAddress()
    {
        $result = app('geocoder')
            ->using('chain')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get()
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals('1600', $result->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $result->getStreetName());
        $this->assertEquals('Washington', $result->getLocality());
        $this->assertEquals('20500', $result->getPostalCode());
    }

    public function testItResolvesAGivenIPAddress()
    {
        $results = app('geocoder')
            ->geocode('72.229.28.185')
            ->get();

        $this->assertTrue($results->isNotEmpty());
        $this->assertEquals('US', $results->first()->getCountry()->getCode());
    }

    public function testItResolvesAGivenAddressWithUmlauts()
    {
        $results = app('geocoder')
            ->geocode('Obere Donaustrasse 22, Wien, Österreich')
            ->get();

        $this->assertEquals('22', $results->first()->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $results->first()->getStreetName());
        $this->assertEquals('Wien', $results->first()->getLocality());
        $this->assertEquals('1020', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItResolvesAGivenAddressWithUmlautsInRegion()
    {
        $results = app('geocoder')
            ->geocode('Obere Donaustraße 22, Wien, Österreich')
            ->get();

        $this->assertEquals('22', $results->first()->getStreetNumber());
        $this->assertEquals('Obere Donaustraße', $results->first()->getStreetName());
        $this->assertEquals('Wien', $results->first()->getLocality());
        $this->assertEquals('1020', $results->first()->getPostalCode());
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItCanUseASpecificProvider()
    {
        $result = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get()
            ->first();

        $this->assertNotNull($result);
        $this->assertEquals('1600', $result->getStreetNumber());
        $this->assertEquals('Pennsylvania Avenue Northwest', $result->getStreetName());
        $this->assertEquals('Washington', $result->getLocality());
        $this->assertEquals('20500', $result->getPostalCode());
    }

    public function testItDumpsAndAddress()
    {
        $results = app('geocoder')
            ->using('google_maps')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
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
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->dump('test');
        $jsonAddress = json_decode($results->first());

        $this->assertEquals('1600', $jsonAddress->properties->streetNumber);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testConfig()
    {
        $this->assertEquals(null, config('geocoder.cache.store'));
        $this->assertEquals(999999999, config('geocoder.cache.duration'));
        $this->assertTrue(is_array($providers = $this->app['config']->get('geocoder.providers')));
        $this->assertCount(3, $providers);
        $this->assertArrayHasKey(GoogleMaps::class, $providers[Chain::class]);
        $this->assertArrayHasKey(GeoPlugin::class, $providers[Chain::class]);
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
        $cacheKey = sha1(
            app('geocoder')->getProvider()->getName()
            . "-" . Str::slug(strtolower(urlencode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')))
        );

        $result = app('geocoder')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get();
        $cachedResult = app('cache')
            ->store(config('geocoder.cache.store'))
            ->get($cacheKey)["value"];
        $isCached = app('cache')
            ->store(config('geocoder.cache.store'))
            ->has($cacheKey);

        $this->assertTrue($isCached);
        $this->assertEquals($result, $cachedResult);
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function testGeocodeQueryProvidesResults()
    {
        $query = GeocodeQuery::create('1600 Pennsylvania Ave NW, Washington, DC 20500, USA');

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
        $results = Geocoder::geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get();

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertTrue($results->isNotEmpty());
    }

    public function testItCanUseMaxMindBinaryWithoutProvider()
    {
        $provider = new MaxMindBinary(__DIR__ . '/../../resources/assets/GeoIP.dat');
        app('geocoder')->registerProvider($provider);

        // dummy assertion, as we are running this test to make sure no
        // exception is thrown in the above provider registration
        $this->assertTrue(true);
    }

    public function testGetNameReturnsString()
    {
        $this->assertEquals('provider_aggregator', app('geocoder')->getName());
    }

    public function testLimitingOfResults()
    {
        $expectedLimit = 1;
        app('geocoder')->limit($expectedLimit);
        $actualLimit = app('geocoder')->getLimit();
        $results = app('geocoder')->using('chain')
            ->geocode('1600 Pennsylvania Ave., Washington, DC USA')
            ->get();

        $this->assertEquals($expectedLimit, $actualLimit);
        $this->assertEquals($expectedLimit, $results->count());
    }

    public function testFetchingAllResults()
    {
        $expectedResults = app('geocoder')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get()
            ->all();
        $actualResults = app('geocoder')
            ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->all();

        $this->assertEquals($expectedResults, $actualResults);
    }

    public function testGetProviders()
    {
        $providers = app('geocoder')->getProviders();

        $this->assertTrue($providers->has('chain'));
        $this->assertTrue($providers->has('bing_maps'));
        $this->assertTrue($providers->has('google_maps'));
    }

    public function testGetProvider()
    {
        $provider = app('geocoder')->getProvider();

        $this->assertEquals($provider->getName(), 'chain');
    }

    public function testJapaneseCharacterGeocoding()
    {
        $cacheKey = sha1(app('geocoder')->getProvider()->getName()
            . "-" . Str::slug(strtolower(urlencode('108-0075 東京都港区港南２丁目１６－３')))
        );

        app('geocoder')
            ->geocode('108-0075 東京都港区港南２丁目１６－３')
            ->get();

        $this->assertTrue(app('cache')->has($cacheKey));
    }

    public function testItProvidesState()
    {
        $results = Geocoder::geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get();

        $this->assertEquals('Washington', $results->first()->getLocality());
    }

    public function testItProvidesAdminLevel()
    {
        $results = Geocoder::geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
            ->get();

        $this->assertEquals('District of Columbia', $results->first()->getAdminLevels()->first()->getName());
    }

    public function testItHandlesOnlyCityAndState()
    {
        $results = Geocoder::geocode('Seatle, WA')->get();

        $this->assertEquals('Seattle', $results->first()->getLocality());
        $this->assertEquals('Washington', $results->first()->getAdminLevels()->first()->getName());
        $this->assertEquals('United States', $results->first()->getCountry()->getName());
    }

    public function testEmptyResultsAreNotCached()
    {
        $cacheKey = md5(Str::slug(strtolower(urlencode('_'))));

        Geocoder::geocode('_')->get();

        $this->assertFalse(app('cache')->has("geocoder-{$cacheKey}"));
    }

    public function testCachingCanBeDisabled()
    {
        $results = app("geocoder")
            ->doNotCache()
            ->geocode('Los Angeles, CA')
            ->get();

        $this->assertEquals(
            "Los Angeles, CA, USA",
            $results->first()->getFormattedAddress()
        );
    }

    public function testGetProviderReturnsCurrentProvider()
    {
        $provider = app("geocoder")
            ->using("google_maps")
            ->getProvider();

        $this->assertEquals("google_maps", $provider->getName());
    }
}
