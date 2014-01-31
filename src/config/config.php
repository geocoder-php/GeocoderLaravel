<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return array(
    /* Providers get called in the chain order given here. The first one to return a result will be used. */
    'providers' => array(
        'Geocoder\Provider\FreeGeoIpProvider'
    ),
    'adapter'  => 'Geocoder\HttpAdapter\CurlHttpAdapter'
);
