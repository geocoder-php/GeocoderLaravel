<?php

use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\Guzzle6HttpAdapter;
use Geocoder\Provider\Chain;
use Geocoder\Provider\BingMaps;
use Geocoder\Provider\FreeGeoIp;
use Geocoder\Provider\GoogleMaps;
use Geocoder\Provider\MaxMindBinary;

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
        Chain::class => [
            GoogleMaps::class => [
                'de-DE',
                'Wien, Österreich',
                true,
                env('GOOGLE_MAPS_API_KEY'),
            ],
            BingMaps::class => [
                'en-US',
                env('BING_MAPS_API_KEY'),
            ],
            FreeGeoIp::class  => [],
        ],
        GoogleMaps::class => [
            'de-DE',
            'Wien, Österreich',
            true,
            env('GOOGLE_MAPS_API_KEY'),
        ],
    ],
    // 'adapter'  => CurlHttpAdapter::class,
    'adapter'  => Guzzle6HttpAdapter::class,
];
