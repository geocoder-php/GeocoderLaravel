<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Geocoder\Tests;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders()
    {
        return array(
            'Toin0u\Geocoder\GeocoderServiceProvider',
        );
    }

    protected function getPackageAliases()
    {
        return array(
            'Geocoder' => 'Toin0u\Geocoder\GeocoderFacade',
        );
    }
}
