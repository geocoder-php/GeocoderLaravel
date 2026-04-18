<?php

use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\Laravel\Facades\Geocoder;
use Geocoder\Laravel\Http\LaravelHttpClient;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\Laravel\Providers\GeocoderService;
use Geocoder\Laravel\Tests\Support\NominatimFixture;
use Geocoder\Model\Coordinates;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\Nominatim\Nominatim;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

beforeEach(function () {
    Http::fake([
        'nominatim.openstreetmap.org/search*' => Http::response(NominatimFixture::whiteHouse()),
        'nominatim.openstreetmap.org/reverse*' => Http::response(NominatimFixture::whiteHouseReverse()),
    ]);
});

it('resolves a given address', function () {
    $result = app('geocoder')
        ->using('chain')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get()
        ->first();

    expect($result)->not->toBeNull();
    expect($result->getStreetNumber())->toBe('1600');
    expect($result->getLocality())->toBe('Washington');
    expect($result->getCountry()->getCode())->toBe('US');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'nominatim.openstreetmap.org/search'));
});

it('can use a specific provider', function () {
    $result = app('geocoder')
        ->using('nominatim')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get()
        ->first();

    expect($result)->not->toBeNull();
    expect($result->getStreetNumber())->toBe('1600');
    expect($result->getLocality())->toBe('Washington');
    expect($result->getCountry()->getCode())->toBe('US');
});

it('dumps an address', function () {
    $results = app('geocoder')
        ->using('nominatim')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->dump('geojson');

    $jsonAddress = json_decode($results->first());

    expect($results->isNotEmpty())->toBeTrue();
    expect($jsonAddress->properties->streetNumber)->toBe('1600');
});

it('throws an exception for invalid dumper', function () {
    app('geocoder')
        ->using('nominatim')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->dump('test');
})->throws(InvalidDumperException::class);

it('loads the expected configuration', function () {
    expect(config('geocoder.cache.store'))->toBe('geocode');
    expect(config('geocoder.cache.duration'))->toBe(999999999);

    $providers = app('config')->get('geocoder.providers');

    expect($providers)->toBeArray();
    expect($providers)->toHaveCount(2);
    expect($providers[Chain::class])->toHaveKey(Nominatim::class);
    expect($providers)->toHaveKey(Nominatim::class);
    expect(app('config')->get('geocoder.adapter'))->toBe(LaravelHttpClient::class);
});

it('registers the geocoder service provider', function () {
    $loadedProviders = app()->getLoadedProviders();

    expect($loadedProviders)->toHaveKey(GeocoderService::class);
    expect($loadedProviders[GeocoderService::class])->toBeTrue();
});

it('binds the provider aggregator', function () {
    expect(app('geocoder'))->toBeInstanceOf(ProviderAndDumperAggregator::class);
});

it('caches geocoding results', function () {
    $cacheKey = sha1(
        app('geocoder')->getProvider()->getName()
        . '-' . Str::slug(strtolower(urlencode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')))
    );

    $result = app('geocoder')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get();
    $cachedResult = app('cache')
        ->store(config('geocoder.cache.store'))
        ->get($cacheKey)['value'];
    $isCached = app('cache')
        ->store(config('geocoder.cache.store'))
        ->has($cacheKey);

    expect($isCached)->toBeTrue();
    expect($cachedResult)->toEqual($result);

    app('geocoder')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get();
    Http::assertSentCount(1);
});

it('returns results from a geocode query', function () {
    $query = GeocodeQuery::create('1600 Pennsylvania Ave NW, Washington, DC 20500, USA');

    $results = app('geocoder')->geocodeQuery($query)->get();

    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->isNotEmpty())->toBeTrue();
});

it('returns results from a reverse query', function () {
    $coordinates = new Coordinates(38.8791981, -76.9818437);
    $query = ReverseQuery::create($coordinates);

    $results = app('geocoder')->reverseQuery($query)->get();

    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->isNotEmpty())->toBeTrue();
    Http::assertSent(fn ($request) => str_contains($request->url(), '/reverse'));
});

it('returns results via the facade', function () {
    $results = Geocoder::geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get();

    expect($results)->toBeInstanceOf(Collection::class);
    expect($results->isNotEmpty())->toBeTrue();
});

it('returns the aggregator name', function () {
    expect(app('geocoder')->getName())->toBe('provider_aggregator');
});

it('respects a result limit', function () {
    $expectedLimit = 1;
    app('geocoder')->limit($expectedLimit);
    $actualLimit = app('geocoder')->getLimit();
    $results = app('geocoder')->using('chain')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get();

    expect($actualLimit)->toBe($expectedLimit);
    expect($results->count())->toBe($expectedLimit);
});

it('returns all results via all()', function () {
    $expectedResults = app('geocoder')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get()
        ->all();
    $actualResults = app('geocoder')
        ->geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->all();

    expect($actualResults)->toEqual($expectedResults);
});

it('exposes the registered providers', function () {
    $providers = app('geocoder')->getProviders();

    expect($providers->has('chain'))->toBeTrue();
});

it('returns the default provider', function () {
    $provider = app('geocoder')->getProvider();

    expect($provider->getName())->toBe('chain');
});

it('provides locality state', function () {
    $results = Geocoder::geocode('1600 Pennsylvania Ave NW, Washington, DC 20500, USA')
        ->get();

    expect($results->first()->getLocality())->toBe('Washington');
});

it('does not cache empty results', function () {
    Http::fake([
        'nominatim.openstreetmap.org/search*' => Http::response([]),
    ]);
    $cacheKey = md5(Str::slug(strtolower(urlencode('_'))));

    Geocoder::geocode('_')->get();

    expect(app('cache')->has("geocoder-{$cacheKey}"))->toBeFalse();
});

it('can disable caching', function () {
    Http::fake([
        'nominatim.openstreetmap.org/search*' => Http::response(NominatimFixture::losAngeles()),
    ]);

    app('geocoder')
        ->doNotCache()
        ->using('nominatim')
        ->geocode('Los Angeles, CA')
        ->get();
    app('geocoder')
        ->doNotCache()
        ->using('nominatim')
        ->geocode('Los Angeles, CA')
        ->get();

    Http::assertSentCount(2);
});

it('returns the current provider after switching', function () {
    $provider = app('geocoder')
        ->using('nominatim')
        ->getProvider();

    expect($provider->getName())->toBe('nominatim');
});
