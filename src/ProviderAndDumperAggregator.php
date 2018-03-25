<?php namespace Geocoder\Laravel;

/**
 * This file is part of the Geocoder Laravel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Mike Bronner <hello@genealabs.com>
 * @license    MIT License
 */

use Geocoder\Dumper\GeoJson;
use Geocoder\Dumper\Gpx;
use Geocoder\Dumper\Kml;
use Geocoder\Dumper\Wkb;
use Geocoder\Dumper\Wkt;
use Geocoder\Geocoder;
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\ProviderAggregator;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\Query;
use Geocoder\Query\ReverseQuery;
use Illuminate\Support\Collection;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProviderAndDumperAggregator
{
    protected $aggregator;
    protected $limit;
    protected $results;

    public function __construct()
    {
        $this->aggregator = new ProviderAggregator();
        $this->results = collect();
    }

    /**
     * @deprecated Use `get()` instead.
     */
    public function all() : array
    {
        return $this->results->all();
    }

    public function get() : Collection
    {
        return $this->results;
    }

    public function dump(string $dumper) : Collection
    {
        $dumperClasses = collect([
            'geojson' => GeoJson::class,
            'gpx' => Gpx::class,
            'kml' => Kml::class,
            'wkb' => Wkb::class,
            'wkt' => Wkt::class,
        ]);

        if (!$dumperClasses->has($dumper)) {
            $errorMessage = implode('', [
                "The dumper specified ('{$dumper}') is invalid. Valid dumpers ",
                "are: geojson, gpx, kml, wkb, wkt.",
            ]);
            throw new InvalidDumperException($errorMessage);
        }

        $dumperClass = $dumperClasses->get($dumper);
        $dumper = new $dumperClass;
        $results = collect($this->results->all());

        return $results->map(function ($result) use ($dumper) {
            return $dumper->dump($result);
        });
    }

    public function geocode(string $value) : self
    {
        $cacheKey = str_slug(strtolower(urlencode($value)));
        $this->results = $this->cacheRequest($cacheKey, [$value], "geocode");

        return $this;
    }

    public function geocodeQuery(GeocodeQuery $query) : self
    {
        $cacheKey = serialize($query);
        $this->results = $this->cacheRequest($cacheKey, [$query], "geocodeQuery");

        return $this;
    }

    public function getLimit() : int
    {
        return $this->limit;
    }

    public function getName() : string
    {
        return $this->aggregator->getName();
    }

    public function limit(int $limit) : self
    {
        $this->aggregator = new ProviderAggregator(null, $limit);
        $this->registerProvidersFromConfig(collect(config('geocoder.providers')));
        $this->limit = $limit;

        return $this;
    }

    /**
     * @deprecated Use `getProviders()` instead.
     */
    public function getProvider()
    {
        return $this->getProviders()->first();
    }

    public function getProviders() : Collection
    {
        return collect($this->aggregator->getProviders());
    }

    public function registerProvider($provider) : self
    {
        $this->aggregator->registerProvider($provider);

        return $this;
    }

    public function registerProviders(array $providers = []) : self
    {
        $this->aggregator->registerProviders($providers);

        return $this;
    }

    public function registerProvidersFromConfig(Collection $providers) : self
    {
        $this->registerProviders($this->getProvidersFromConfiguration($providers));

        return $this;
    }

    public function reverse(float $latitude, float $longitude) : self
    {
        $cacheKey = str_slug(strtolower(urlencode("{$latitude}-{$longitude}")));
        $this->results = $this->cacheRequest($cacheKey, [$latitude, $longitude], "reverse");

        return $this;
    }

    public function reverseQuery(ReverseQuery $query) : self
    {
        $cacheKey = serialize($query);
        $this->results = $this->cacheRequest($cacheKey, [$query], "reverseQuery");

        return $this;
    }

    public function using(string $name) : self
    {
        $this->aggregator = $this->aggregator->using($name);

        return $this;
    }

    protected function cacheRequest(string $cacheKey, array $queryElements, string $queryType)
    {
        $hashedCacheKey = sha1($cacheKey);
        $duration = config("geocoder.cache.duration", 0);
        $store = config('geocoder.cache.store');

        $result = app("cache")
            ->store($store)
            ->remember($hashedCacheKey, $duration, function () use ($cacheKey, $queryElements, $queryType) {
                return [
                    "key" => $cacheKey,
                    "value" => collect($this->aggregator->{$queryType}(...$queryElements)),
                ];
            });
        $result = $this->preventCacheKeyHashCollision(
            $result,
            $hashedCacheKey,
            $cacheKey,
            $queryElements,
            $queryType
        );
        $this->removeEmptyCacheEntry($result, $hashedCacheKey);

        return $result;
    }

    protected function getAdapterClass(string $provider) : string
    {
        $specificAdapters = collect([
            'Geocoder\Provider\GeoIP2\GeoIP2' => 'Geocoder\Provider\GeoIP2\GeoIP2Adapter',
            'Geocoder\Provider\MaxMindBinary\MaxMindBinary' => '',
        ]);

        if ($specificAdapters->has($provider)) {
            return $specificAdapters->get($provider);
        }

        return config('geocoder.adapter');
    }

    protected function getArguments(array $arguments, string $provider) : array
    {
        if ($provider === 'Geocoder\Provider\Chain\Chain') {
            return $this->getProvidersFromConfiguration(
                collect(config('geocoder.providers.Geocoder\Provider\Chain\Chain'))
            );
        }

        $adapter = $this->getAdapterClass($provider);
        $reader = null;

        if ($adapter) {
            if ($this->requiresReader($provider)) {
                $reader = config('geocoder.reader');
            }

            array_unshift($arguments, new $adapter($reader));
        }

        return $arguments;
    }

    protected function getProvidersFromConfiguration(Collection $providers) : array
    {
        $providers = $providers->map(function ($arguments, $provider) {
            $arguments = $this->getArguments($arguments, $provider);
            $reflection = new ReflectionClass($provider);

            if ($provider === 'Geocoder\Provider\Chain\Chain') {
                return $reflection->newInstance($arguments);
            }

            return $reflection->newInstanceArgs($arguments);
        });

        return $providers->toArray();
    }

    protected function preventCacheKeyHashCollision(
        array $result,
        string $hashedCacheKey,
        string $cacheKey,
        array $queryElements,
        string $queryType
    ) {
        if ($result["key"] === $cacheKey) {
            return $result["value"];
        }

        app("cache")
            ->store(config('geocoder.cache.store'))
            ->forget($hashedCacheKey);

        return $this->cacheRequest($cacheKey, $queryElements, $queryType);
    }

    protected function removeEmptyCacheEntry(Collection $result, string $cacheKey)
    {
        if ($result && $result->isEmpty()) {
            app('cache')->forget($cacheKey);
        }
    }

    protected function requiresReader(string $class) : bool
    {
        $specificAdapters = collect([
            'Geocoder\Provider\GeoIP2\GeoIP2',
        ]);

        return $specificAdapters->contains($class);
    }
}
