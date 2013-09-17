<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Geocoder;

use Illuminate\Support\Facades\Facade;

/**
 * Facade for Geocoder
 *
 * @author Antoine Corcy <contact@sbin.dk>
 */
class GeocoderFacade extends Facade
{
    /**
    * Get the registered name of the component.
    *
    * @return string
    */
    protected static function getFacadeAccessor()
    {
        return 'geocoder';
    }
}
