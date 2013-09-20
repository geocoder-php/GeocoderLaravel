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
    'provider' => 'Geocoder\Provider\FreeGeoIpProvider',
    'adapter'  => 'Geocoder\HttpAdapter\CurlHttpAdapter'
);
