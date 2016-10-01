<?php

use Ivory\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\BingMaps;
use Geocoder\Provider\FreeGeoIp;
use Geocoder\Provider\GoogleMaps;

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    // Providers get called in the chain order given here.
    // The first one to return a result will be used.
    'providers' => [
        GoogleMaps::class => null,
        BingMaps::class => [
            'en-US',
            env('BING_MAPS_API_KEY'),
        ],
        FreeGeoIp::class  => null,
    ],
    'adapter'  => CurlHttpAdapter::class,
];
