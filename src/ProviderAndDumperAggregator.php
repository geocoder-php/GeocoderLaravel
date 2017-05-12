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
use Geocoder\ProviderAggregator;
use Illuminate\Support\Collection;

/**
 * @author Mike Bronner <hello@genealabs.com>
 */
class ProviderAndDumperAggregator extends ProviderAggregator implements Geocoder
{
    /**
     * @var \Geocoder\Model\AddressCollection
     */
    protected $results;

    /**
     * @return array
     */
    public function all()
    {
        return $this->results->all();
    }

    /**
     * @param $dumper
     * @return Collection
     * @throws InvalidDumperException
     */
    public function dump($dumper)
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

    /**
     * @param string
     * @return ProviderAndDumperAggregator
     */
    public function geocode($value)
    {
        $this->results = parent::geocode($value);

        return $this;
    }

    /**
     * @return \Geocoder\Model\AddressCollection
     */
    public function get()
    {
        return $this->results;
    }

    /**
     * @param float
     * @param float
     * @return ProviderAndDumperAggregator
     */
    public function reverse($latitude, $longitude)
    {
        $this->results = parent::reverse($latitude, $longitude);

        return $this;
    }

    /**
     * @param $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->registerProviders(array_map(function ($provider) use ($locale) {
            if (is_callable([$provider, 'setLocale'])) {
                return $provider->setLocale($locale);
            }

            return $provider;
        }, $this->getProviders()));

        return $this;
    }
}
