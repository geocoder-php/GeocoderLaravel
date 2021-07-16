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
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\ProviderAggregator;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Illuminate\Log\Logger;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Psr\Log\LoggerAwareTrait;
use ReflectionClass;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProviderAndDumperAggregator
{
    protected $aggregator;
    protected $limit;
    protected $results;
    protected $isCaching = true;

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

    public function toJson() : string
    {
        return $this
            ->dump("geojson")
            ->first();
    }

    public function doNotCache() : self
    {
        $this->isCaching = false;

        return $this;
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
        $cacheKey = (new Str)->slug(strtolower(urlencode($value)));
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

    public function getProvider()
    {
        $reflectedClass = new ReflectionClass(ProviderAggregator::class);
        $reflectedProperty = $reflectedClass->getProperty('provider');
        $reflectedProperty->setAccessible(true);

        return $reflectedProperty->getValue($this->aggregator)
            ?? $this->getProviders()->first();
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
        $cacheKey = (new Str)->slug(strtolower(urlencode("{$latitude}-{$longitude}")));
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
        if (! $this->isCaching) {
            $this->isCaching = true;

            return collect($this->aggregator->{$queryType}(...$queryElements));
        }

        $hashedCacheKey = sha1($this->getProvider()->getName() . "-" . $cacheKey);
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

    protected function getReader()
    {
        $reader = config('geocoder.reader');

        if (is_array(config('geocoder.reader'))) {
            $readerClass = array_key_first(config('geocoder.reader'));
            $readerArguments = config('geocoder.reader')[$readerClass];
            $reflection = new ReflectionClass($readerClass);
            $reader = $reflection->newInstanceArgs($readerArguments);
        }

        return $reader;
    }

    protected function getArguments(array $arguments, string $provider) : array
    {
        if ($provider === 'Geocoder\Provider\Chain\Chain') {
            return $this->getProvidersFromConfiguration(
                collect(config('geocoder.providers.Geocoder\Provider\Chain\Chain'))
            );
        }

        $adapter = $this->getAdapterClass($provider);

        if ($adapter) {
            if ($this->requiresReader($provider)) {
                $adapter = new $adapter($this->getReader());
            } else {
                $adapter = new $adapter;
            }

            array_unshift($arguments, $adapter);
        }

        return $arguments;
    }

    protected function getProvidersFromConfiguration(Collection $providers) : array
    {
        $providers = $providers->map(function ($arguments, $provider) {
            $arguments = $this->getArguments($arguments, $provider);
            $reflection = new ReflectionClass($provider);

            if ($provider === "Geocoder\Provider\Chain\Chain") {
                $chainProvider = $reflection->newInstance($arguments);

                if (class_exists(Logger::class)
                    && in_array(LoggerAwareTrait::class, class_uses($chainProvider))
                    && app(Logger::class) !== null
                ) {
                    $chainProvider->setLogger(app(Logger::class));
                }

                return $chainProvider;
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
            app('cache')
                ->store(config('geocoder.cache.store'))
                ->forget($cacheKey);
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
