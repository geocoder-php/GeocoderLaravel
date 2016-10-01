<?php

/**
 * This file is part of the GeocoderLaravel library.
 *
 * (c) Antoine Corcy <contact@sbin.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Toin0u\Tests\Geocoder;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            'Toin0u\Geocoder\GeocoderServiceProvider',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getPackageAliases($app)
    {
        return [
            'Geocoder' => 'Toin0u\Geocoder\Facade\Geocoder',
        ];
    }
}
