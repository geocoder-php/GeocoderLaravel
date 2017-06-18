<?php namespace Geocoder\Laravel;

/**
 * This file is part of the Geocoder package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

use Geocoder\Dumper\GeoJson;
use Geocoder\Dumper\Gpx;
use Geocoder\Dumper\Kml;
use Geocoder\Dumper\Wkb;
use Geocoder\Dumper\Wkt;
use Geocoder\Geocoder;
use Geocoder\Laravel\Exceptions\InvalidDumperException;
use Geocoder\Laravel\ProviderAndDumperAggregator;
use Geocoder\ProviderAggregator;
use Geocoder\Model\AddressCollection;
use Illuminate\Support\Collection;

/**
 * @author Mike Bronner <hello@genealabs.com>
 */
class ProviderAndDumperAggregator
{
    protected $results;
    protected $aggregator;

    public function __construct(int $limit = Geocoder::DEFAULT_RESULT_LIMIT)
    {
        $this->aggregator = new ProviderAggregator($limit);
    }

    public function all() : array
    {
        return $this->results->all();
    }

    public function get() : AddressCollection
    {
        return $this->results;
    }

    public function dump($dumper) : Collection
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

    public function geocodeQuery($query)
    {
        return $this->aggregator->geocodeQuery($query);
    }

    public function reverseQuery($query)
    {
        return $this->aggregator->reverseQuery($query);
    }

    public function getName()
    {
        return $this->aggregator->getName();
    }

    public function geocode(string $value) : self
    {
        $cacheId = str_slug($value);
        $this->results = cache()->remember(
            "geocoder-{$cacheId}",
            config('geocoder.cache-duraction', 0),
            function () use ($value) {
                return $this->aggregator->geocode($value);
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
                return $this->aggregator->reverse($latitude, $longitude);
            }
        );

        return $this;
    }

    public function limit($limit)
    {
        $this->aggregator->limit($limit);

        return $this;
    }

    public function getLimit()
    {
        return $this->aggregator->getLimit();
    }

    public function registerProvider($provider)
    {
        $this->aggregator->registerProvider($provider);

        return $this;
    }

    public function registerProviders($providers = [])
    {
        $this->aggregator->registerProviders($providers);

        return $this;
    }

    public function using($name)
    {
        $this->aggregator->using($name);

        return $this;
    }

    public function getProviders()
    {
        return $this->aggregator->getProviders();
    }

    protected function getProvider()
    {
        return $this->aggregator->getProvider();
    }
}
