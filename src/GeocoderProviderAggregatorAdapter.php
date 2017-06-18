<?php namespace Geocoder\Laravel;

/**
 * This file is part of the GeocoderLaravel library.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Geocoder\ProviderAggregator;

/**
 * @author Mike Bronner <hello@genealabs.com>
 */
class GeocoderProviderAggregatorAdapter
{
    protected $aggregator;

    public function __construct(int $limit = Geocoder::DEFAULT_RESULT_LIMIT)
    {
        $this->aggregator = new ProviderAggregator($limit);
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

    public function geocode($value)
    {
        return $this->aggregator->geocode($value);
    }

    public function reverse(float $latitude, float $longitude)
    {
        return $this->aggregator->reverse($latitude, $longitude);
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
