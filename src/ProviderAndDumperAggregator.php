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
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\ProviderAggregator;
use Illuminate\Support\Collection;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ProviderAndDumperAggregator
{
    protected $aggregator;
    protected $results;

    public function __construct(int $limit = Geocoder::DEFAULT_RESULT_LIMIT)
    {
        $this->aggregator = new ProviderAggregator($limit);
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

    public function geocodeQuery(GeocodeQuery $query) : self
    {
        $cacheKey = serialize($query);
        $this->results = cache()->remember(
            "geocoder-{$cacheKey}",
            config('geocoder.cache-duraction', 0),
            function () use ($query) {
                return collect($this->aggregator->geocodeQuery($query));
            }
        );

        return $this;
    }

    public function reverseQuery(ReverseQuery $query) : self
    {
        $cacheKey = serialize($query);
        $this->results = cache()->remember(
            "geocoder-{$cacheKey}",
            config('geocoder.cache-duraction', 0),
            function () use ($query) {
                return collect($this->aggregator->reverseQuery($query));
            }
        );

        return $this;
    }

    public function getName() : string
    {
        return $this->aggregator->getName();
    }

    public function geocode(string $value) : self
    {
        $cacheKey = str_slug($value);
        $this->results = cache()->remember(
            "geocoder-{$cacheKey}",
            config('geocoder.cache-duraction', 0),
            function () use ($value) {
                return collect($this->aggregator->geocode($value));
            }
        );

        return $this;
    }

    public function reverse(float $latitude, float $longitude) : self
    {
        $cacheId = str_slug("{$latitude}-{$longitude}");
        $this->results = cache()->remember(
            "geocoder-{$cacheId}",
            config('geocoder.cache-duraction', 0),
            function () use ($latitude, $longitude) {
                return collect($this->aggregator->reverse($latitude, $longitude));
            }
        );

        return $this;
    }

    public function limit(int $limit) : self
    {
        $this->aggregator->limit($limit);

        return $this;
    }

    public function getLimit() : int
    {
        return $this->aggregator->getLimit();
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

    public function using(string $name) : self
    {
        $this->aggregator->using($name);

        return $this;
    }

    public function getProviders() : Collection
    {
        return collect($this->aggregator->getProviders());
    }

    protected function getProvider()
    {
        return $this->aggregator->getProvider();
    }
}
