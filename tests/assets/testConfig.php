<?php

/**
 * This file is part of the GeocoderLaravel library.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Http\Client\Curl\Client;
use Geocoder\Provider\BingMaps\BingMaps;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\GeoPlugin\GeoPlugin;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Provider\HostIp\HostIp;

return [
    'cache-duration' => 999999999,
    'providers' => [
        Chain::class => [
            GoogleMaps::class => [
                'en-US',
                env('GOOGLE_MAPS_API_KEY'),
            ],
            GeoPlugin::class  => [],
        ],
        BingMaps::class => [
            'en-US',
            env('BING_MAPS_API_KEY'),
        ],
        GoogleMaps::class => [
            'us',
            env('GOOGLE_MAPS_API_KEY'),
        ],
    ],
    'adapter'  => Client::class,
];
