<?php

/**
 * This file is part of the GeocoderLaravel library.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Geocoder\Laravel\Http\LaravelHttpClient;
use Geocoder\Provider\Chain\Chain;
use Geocoder\Provider\Nominatim\Nominatim;

return [
    'cache' => [
        'store' => null,
        'duration' => 999999999,
    ],
    'providers' => [
        Chain::class => [
            Nominatim::class => [
                'https://nominatim.openstreetmap.org',
                'GeocoderLaravel-Tests/1.0 (https://github.com/geocoder-php/GeocoderLaravel)',
            ],
        ],
        Nominatim::class => [
            'https://nominatim.openstreetmap.org',
            'GeocoderLaravel-Tests/1.0 (https://github.com/geocoder-php/GeocoderLaravel)',
        ],
    ],
    'adapter' => LaravelHttpClient::class,
    'reader' => [],
];
